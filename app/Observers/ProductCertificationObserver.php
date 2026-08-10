<?php

namespace App\Observers;

use App\Enums\CertificationStatus;
use App\Enums\HalalStatus;
use App\Models\ProductCertification;
use App\Models\ProductHalalProfile;

class ProductCertificationObserver
{
    public function saved(ProductCertification $certification): void
    {
        $this->syncProfile($certification->product_id);
    }

    public function deleted(ProductCertification $certification): void
    {
        $this->syncProfile($certification->product_id);
    }

    /**
     * `product_halal_profiles.is_certified` is the flag the storefront badge reads,
     * so it must never be stale.
     */
    private function syncProfile(?int $productId): void
    {
        if (! $productId) {
            return;
        }

        $isCertified = ProductCertification::where('product_id', $productId)
            ->where('status', CertificationStatus::Active)
            ->exists();

        $profile = ProductHalalProfile::firstOrNew(['product_id' => $productId]);

        $profile->is_certified = $isCertified;

        // An active third-party certificate settles the overall status; losing it
        // drops an unreviewed product back to "unknown" rather than leaving a stale claim.
        if ($isCertified) {
            $profile->overall_status = HalalStatus::Halal;
            $profile->is_self_declared = false;
        } elseif ($profile->overall_status === HalalStatus::Halal && ! $profile->is_self_declared) {
            $profile->overall_status = HalalStatus::Unknown;
        }

        $profile->overall_status ??= HalalStatus::Unknown;
        $profile->save();
    }
}
