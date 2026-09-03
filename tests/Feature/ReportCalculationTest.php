<?php

namespace Tests\Feature;

use App\Filament\Pages\Report;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_sales_only_count_completed_quantity_and_support_legacy_prices(): void
    {
        $service = Service::create(['name' => 'Deep Clean', 'price' => 25000]);
        $customer = Customer::create(['name' => 'Budi']);

        $this->order($customer, $service, 'completed', price: 25000, quantity: 2, total: 50000);
        $this->order($customer, $service, 'completed', price: 50000, quantity: 2, total: 50000);
        $this->order($customer, $service, 'cancelled', price: 25000, quantity: 10, total: 250000);

        $page = new class extends Report
        {
            public function reportData(): array
            {
                return $this->getViewData();
            }
        };
        $page->filterPeriod = 'this_month';
        $page->filterMonth = now()->format('m');
        $page->filterYear = now()->format('Y');

        $sales = $page->reportData()['serviceSales'];

        $this->assertCount(1, $sales);
        $this->assertSame(4, $sales[0]['quantity']);
        $this->assertSame(100000.0, $sales[0]['revenue']);
    }

    private function order(
        Customer $customer,
        Service $service,
        string $status,
        float $price,
        int $quantity,
        float $total,
    ): Order {
        return Order::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'services' => [[
                'service_id' => $service->id,
                'price' => $price,
                'quantity' => $quantity,
                'description' => $service->name,
            ]],
            'total_price' => $total,
            'discount' => 0,
            'status' => $status,
            'estimated_finished_date' => now()->addDay()->toDateString(),
        ]);
    }
}
