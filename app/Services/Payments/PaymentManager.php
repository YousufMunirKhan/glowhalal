<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentContext;
use App\Contracts\Payments\PaymentDriver;
use App\Exceptions\UnknownPaymentDriverException;
use Illuminate\Contracts\Container\Container;

/** Architecture §6.4. */
final class PaymentManager
{
    /** @var array<string, PaymentDriver> */
    private array $resolved = [];

    public function __construct(
        private readonly Container $container,
        /** @var array<string, array{driver: class-string<PaymentDriver>, enabled: bool, config: array}> */
        private readonly array $config,
    ) {}

    public function driver(string $key): PaymentDriver
    {
        return $this->resolved[$key] ??= $this->resolve($key);
    }

    public function has(string $key): bool
    {
        return isset($this->config[$key]) && ($this->config[$key]['enabled'] ?? false);
    }

    /** @return array<int, PaymentDriver> Ordered, filtered to what this basket may use. */
    public function availableFor(PaymentContext $context): array
    {
        return collect($this->config)
            ->filter(fn (array $c) => $c['enabled'] ?? false)
            ->keys()
            ->map(fn (string $key) => $this->driver($key))
            ->filter(fn (PaymentDriver $d) => $d->isAvailableFor($context))
            ->values()
            ->all();
    }

    private function resolve(string $key): PaymentDriver
    {
        $config = $this->config[$key]
            ?? throw new UnknownPaymentDriverException("No payment driver registered for [{$key}].");

        return $this->container->make($config['driver'], ['config' => $config['config'] ?? []]);
    }
}
