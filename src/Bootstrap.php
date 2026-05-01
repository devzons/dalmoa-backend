<?php
declare(strict_types=1);

namespace DalmoaCore;

use DalmoaCore\Admin\ListTable\AdminColumns;
use DalmoaCore\Admin\ListTable\AdminQuickActions;
use DalmoaCore\Admin\Dashboard\AdDashboardRegistrar;
use DalmoaCore\Admin\AdPlanMetaBox;
use DalmoaCore\Admin\MetaBoxes\AdAbTestMetaBox;
use DalmoaCore\Api\Routes\AdRoutes;
// use DalmoaCore\Api\Routes\AdAnalyticsRoutes;
use DalmoaCore\Api\Routes\AdTrackingRoutes;
use DalmoaCore\Api\Routes\BusinessPageRoutes;
use DalmoaCore\Api\Routes\BusinessSaleRoutes;
use DalmoaCore\Api\Routes\CarRoutes;
use DalmoaCore\Api\Routes\DirectoryRoutes;
use DalmoaCore\Api\Routes\JobRoutes;
use DalmoaCore\Api\Routes\LoanRoutes;
use DalmoaCore\Api\Routes\MarketplaceRoutes;
use DalmoaCore\Api\Routes\NewsRoutes;
use DalmoaCore\Api\Routes\RealEstateRoutes;
use DalmoaCore\Api\Routes\SearchRoutes;
use DalmoaCore\Api\Routes\TownBoardRoutes;
use DalmoaCore\Api\Routes\SubmitRoutes;
use DalmoaCore\Api\Routes\PaymentRoutes;
use DalmoaCore\Fields\Admin\MetaBoxRegistrar;
use DalmoaCore\Fields\Admin\MetaBoxRenderer;
use DalmoaCore\Fields\Persistence\MetaSaver;
use DalmoaCore\Fields\Registry\MetaRegistry;
use DalmoaCore\Fields\Schemas\AdListingFieldSchema;
use DalmoaCore\Fields\Schemas\BusinessPageFieldSchema;
use DalmoaCore\Fields\Schemas\BusinessSaleFieldSchema;
use DalmoaCore\Fields\Schemas\CarFieldSchema;
use DalmoaCore\Fields\Schemas\DirectoryFieldSchema;
use DalmoaCore\Fields\Schemas\JobFieldSchema;
use DalmoaCore\Fields\Schemas\LoanFieldSchema;
use DalmoaCore\Fields\Schemas\MarketplaceFieldSchema;
use DalmoaCore\Fields\Schemas\NewsFieldSchema;
use DalmoaCore\Fields\Schemas\RealEstateFieldSchema;
use DalmoaCore\Fields\Schemas\TownBoardFieldSchema;
use DalmoaCore\PostTypes\AdListingPostType;
use DalmoaCore\PostTypes\BusinessPagePostType;
use DalmoaCore\PostTypes\BusinessSalePostType;
use DalmoaCore\PostTypes\CarPostType;
use DalmoaCore\PostTypes\DirectoryPostType;
use DalmoaCore\PostTypes\JobPostType;
use DalmoaCore\PostTypes\LoanPostType;
use DalmoaCore\PostTypes\MarketplacePostType;
use DalmoaCore\PostTypes\NewsPostType;
use DalmoaCore\PostTypes\RealEstatePostType;
use DalmoaCore\PostTypes\TownBoardPostType;
use DalmoaCore\Support\Hooks\AdLifecycleHook;

final class Bootstrap
{
    /**
     * @var array<class-string>
     */
    private array $schemaClasses = [
        DirectoryFieldSchema::class,
        AdListingFieldSchema::class,
        BusinessPageFieldSchema::class,
        BusinessSaleFieldSchema::class,
        NewsFieldSchema::class,
        JobFieldSchema::class,
        LoanFieldSchema::class,
        MarketplaceFieldSchema::class,
        RealEstateFieldSchema::class,
        CarFieldSchema::class,
        TownBoardFieldSchema::class,
    ];

    /**
     * @var array<string, class-string>
     */
    private array $postTypeSchemaMap = [
        'directory' => DirectoryFieldSchema::class,
        'ad_listing' => AdListingFieldSchema::class,
        'business_page' => BusinessPageFieldSchema::class,
        'business_sale' => BusinessSaleFieldSchema::class,
        'news' => NewsFieldSchema::class,
        'job' => JobFieldSchema::class,
        'loan' => LoanFieldSchema::class,
        'marketplace' => MarketplaceFieldSchema::class,
        'real_estate' => RealEstateFieldSchema::class,
        'car' => CarFieldSchema::class,
        'town_board' => TownBoardFieldSchema::class,
    ];

    /**
     * @var string[]
     */
    private array $adminColumnPostTypes = [
        'directory',
        'ad_listing',
        'business_page',
        'business_sale',
        'news',
        'job',
        'loan',
        'marketplace',
        'real_estate',
        'car',
        'town_board',
    ];

    public function init(): void
    {
        $this->registerPostTypes();
        $this->disableBlockEditorForDalmoaPostTypes();
        $this->registerMetaRegistry();
        $this->registerMetaBoxes();
        $this->registerAdPlanMetaBox();
        $this->registerAbTestMetaBox();
        $this->registerMetaSaving();
        $this->registerAdminAssets();
        $this->registerAdminColumns();
        $this->registerAdminDashboard();
        $this->registerRestRoutes();
        $this->registerLifecycleHooks();
    }

    private function registerAdminDashboard(): void
    {
        (new AdDashboardRegistrar())->register();
    }

    private function registerAbTestMetaBox(): void
    {
        (new AdAbTestMetaBox())->register();
    }

    private function registerLifecycleHooks(): void
    {
        add_action('init', function (): void {
            (new AdLifecycleHook())->register();
        });
    }

    private function registerPostTypes(): void
    {
        add_action('init', [DirectoryPostType::class, 'register']);
        add_action('init', [AdListingPostType::class, 'register']);
        add_action('init', [BusinessPagePostType::class, 'register']);
        add_action('init', [BusinessSalePostType::class, 'register']);
        add_action('init', [NewsPostType::class, 'register']);
        add_action('init', [JobPostType::class, 'register']);
        add_action('init', [LoanPostType::class, 'register']);
        add_action('init', [MarketplacePostType::class, 'register']);
        add_action('init', [RealEstatePostType::class, 'register']);
        add_action('init', [CarPostType::class, 'register']);
        add_action('init', [TownBoardPostType::class, 'register']);
    }

    private function registerMetaRegistry(): void
    {
        add_action('init', function (): void {
            (new MetaRegistry())->register($this->schemaClasses);
        });
    }

    private function registerMetaBoxes(): void
    {
        add_action('add_meta_boxes', function (): void {
            (new MetaBoxRegistrar(new MetaBoxRenderer()))->register($this->schemaClasses);
        });
    }

    private function registerAdPlanMetaBox(): void
    {
        (new AdPlanMetaBox())->register();
    }

    private function registerMetaSaving(): void
    {
        foreach ($this->postTypeSchemaMap as $postType => $schemaClass) {
            add_action("save_post_{$postType}", function (int $postId) use ($schemaClass): void {
                (new MetaSaver())->save($postId, $schemaClass);
            });
        }
    }

    private function registerAdminAssets(): void
    {
        add_action('admin_enqueue_scripts', function (): void {
            wp_enqueue_media();
        });
    }

    private function registerAdminColumns(): void
    {
        add_action('admin_init', function (): void {
            (new AdminColumns())->register($this->adminColumnPostTypes);
            (new AdminQuickActions())->register($this->adminColumnPostTypes);
        });
    }

    private function registerRestRoutes(): void
    {
        add_action('rest_api_init', function (): void {
            (new DirectoryRoutes())->register();
            (new AdRoutes())->register();
            (new AdTrackingRoutes())->register();
            (new BusinessPageRoutes())->register();
            (new BusinessSaleRoutes())->register();
            (new NewsRoutes())->register();
            (new JobRoutes())->register();
            (new LoanRoutes())->register();
            (new MarketplaceRoutes())->register();
            (new RealEstateRoutes())->register();
            (new CarRoutes())->register();
            (new TownBoardRoutes())->register();
            (new SearchRoutes())->register();
            (new SubmitRoutes())->register();
            (new PaymentRoutes())->register();
            // (new AdAnalyticsRoutes())->register();
        });
    }

    private function disableBlockEditorForDalmoaPostTypes(): void
    {
        add_filter('use_block_editor_for_post_type', function (bool $useBlockEditor, string $postType): bool {
            if (in_array($postType, $this->adminColumnPostTypes, true)) {
                return false;
            }

            return $useBlockEditor;
        }, 10, 2);
    }
}