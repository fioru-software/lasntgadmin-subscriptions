<?php

namespace Lasntg\Admin\Subscriptions\OptionPages;

abstract class OptionPage
{
    protected static $options;
    public static $optionName;
    protected static $optionsSanitized = false;

    /**
     * Slug of currently active tab
     *
     * @var string
     */
    protected static $activeTab = '';

    /**
     * @var string Slug of this tab
     */
    protected static $tabName;

    public static function get_option_name()
    {
        return static::$optionName;
    }

    public static function load_page_content()
    {
?>
        <form method="post" action="options.php">
            <?php
            settings_fields(static::$optionName);
            do_settings_sections(static::$optionName);
            submit_button();
            ?>
        </form>
    <?php
    }
    public static function init()
    {
        static::$activeTab = isset($_GET['tab']) ? $_GET['tab'] : static::$activeTab;
        add_action('admin_menu', [static::class, 'add_plugin_page']);
    }
    abstract public static function page_init();
    abstract public static function section_info();
    abstract public static function sanitize($input): array;

    protected static function register_setting()
    {
        register_setting(
            static::$optionName,                                  // option_group
            static::$optionName,                              // option_name
            [static::class, 'sanitize']                             // sanitize_callback
        );
        add_settings_section(
            'message_settings',                             // id
            '',                                             // title
            [static::class, 'section_info'],                        // callback
            static::$optionName                    // page
        );
    }


    /**
     * Adds plugin settings page to admin
     */
    public static function add_plugin_page()
    {
        add_options_page(
            __('Lasntg Subscriptions', 'lasngtadmin'), // page_title
            __('Lasntg Subscriptions', 'lasngtadmin'), // menu_title
            'manage_options',                               // capability
            'lasntg-subscriptions',                           // menu_slug
            [self::class, 'create_admin_page']                    // callback function
        );
    }


    /**
     * Creates header of admin settings page
     * Expects loadPageContent() to exist in child class
     */
    public static function create_admin_page()
    {
    ?><div class="wrap">
            <h2><?= __('Lasntg Subscriptions', 'lasngtadmin') ?></h2>

            <h2 class="nav-tab-wrapper">
                <a href="?page=lasntg-subscriptions&tab=national_manager" class="nav-tab <?php echo static::$activeTab == 'national_manager' ? 'nav-tab-active' : '' ?>"><?= __('Messages', 'lasngtadmin') ?></a>
                <a href="?page=lasntg-subscriptions&tab=training_officers" class="nav-tab <?php echo static::$activeTab == 'training_officers' ? 'nav-tab-active' : '' ?>"><?= __('Training Officers', 'lasngtadmin') ?></a>
                <a href="?page=lasntg-subscriptions&tab=advanced" class="nav-tab <?php echo static::$activeTab == 'advanced' ? 'nav-tab-active' : '' ?>"><?= __('Settings', 'lasngtadmin') ?></a>
            </h2>

            <?php call_user_func([self::class, 'load_page_content']); ?>
        </div><?php
            }
        }
