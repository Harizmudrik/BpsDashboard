<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\BpsStatistic;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the homepage response.
     */
    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHasAll(['cards', 'provincesData']);
    }

    /**
     * Test detail page returns successful response for valid metric.
     */
    public function test_detail_page_returns_successful_response_for_valid_metric(): void
    {
        $response = $this->get('/metric/tpt');

        $response->assertStatus(200);
        $response->assertViewIs('detail');
        $response->assertViewHasAll(['metric', 'config', 'tahuns', 'periodes', 'provinsis']);
    }

    /**
     * Test detail page returns 404 for invalid metric.
     */
    public function test_detail_page_returns_404_for_invalid_metric(): void
    {
        $response = $this->get('/metric/invalid_metric_name');

        $response->assertStatus(404);
    }

    /**
     * Test the data API endpoint for provincial and national metrics.
     */
    public function test_data_api_endpoint_returns_json_structure(): void
    {
        $response = $this->get('/api/data/tpt');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'summary' => [
                'value',
                'avg_val',
                'max_val',
                'max_name',
                'min_val',
                'min_name',
                'current_year',
                'current_periode',
                'trend',
            ],
            'lineChart' => [
                'labels',
                'datasets' => [
                    '*' => [
                        'label',
                        'data',
                    ]
                ]
            ],
            'barChart' => [
                'labels',
                'datasets' => [
                    '*' => [
                        'label',
                        'data',
                    ]
                ]
            ],
            'mapData',
            'tableData',
        ]);
    }

    /**
     * Test autocomplete search suggestions.
     */
    public function test_search_suggestions_api_endpoint(): void
    {
        $response = $this->get('/api/search-suggestions?q=infl');

        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }

    /**
     * Test compare data API endpoint.
     */
    public function test_compare_data_api_endpoint(): void
    {
        $response = $this->get('/api/compare/tpt?base_region=9999&compare_with=1100');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'activeYear',
            'activePeriode',
            'base' => ['code', 'name', 'value'],
            'compare' => ['code', 'name', 'value'],
            'delta',
            'lineChart' => [
                'labels',
                'datasets' => [
                    '*' => [
                        'label',
                        'data',
                    ]
                ]
            ]
        ]);
    }
}
