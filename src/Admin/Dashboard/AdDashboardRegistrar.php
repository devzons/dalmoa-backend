<?php
declare(strict_types=1);

namespace DalmoaCore\Admin\Dashboard;

final class AdDashboardRegistrar
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu']);
    }

    public function addMenu(): void
    {
        add_menu_page(
            '광고 대시보드',
            'Ads Dashboard',
            'manage_options',
            'dalmoa-ads-dashboard',
            [$this, 'renderPage'],
            'dashicons-megaphone',
            26
        );
    }

    public function renderPage(): void
    {
        (new AdDashboardPage())->render();
    }
}