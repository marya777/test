<?php

class CE_Welcome extends VG_Welcome
{
    function __construct()
    {
        parent::__construct();
        $this->title = 'Welcome to ClassifiedEngine <strong>'.ET_VERSION.'</strong>';
        $this->description = 'Futuristic designed Classified Ads Theme. Tailor-made to monetize ads. Comes with simple user experience and powerful built-in design tools.';
        $this->pages = array(
            'ce-whatisnew' => array(
                'page_title' => 'What\'s new',
                'content_callback' => 'whatisnew_page_content',
                'is_visible' => false,
            ),
            'ce-tutorials' => array(
                'page_title' => 'CE Tutorials',
                'content_callback' => 'tutorials_page_content',
                'is_visible' => false,
            ),
            'ce-changelog' => array(
                'page_title' => 'Change log',
                'content_callback' => 'changelog_page_content',
                'is_visible' => false,
            ),
            'ce-about' => array(
                'page_title' => 'About CE',
                'content_callback' => 'about_page_content',
                'is_visible' => false,
            )
        );
    }

    public function tutorials_page_content()
    {
        include_once(ABSPATH . WPINC . '/feed.php');
        $rss = fetch_feed('http://support.enginethemes.com/customer/portal/topics/606065-classifiedengine-tutorials/articles.rss');
        if (!is_wp_error($rss)) : // Checks that the object is created correctly
            $rss->enable_order_by_date(false);
            $maxitems = $rss->get_item_quantity(20);
            $rss_items = $rss->get_items(0, $maxitems);
            ?>
            <div class="what-is-new">
                <?php foreach ($rss_items as $item) : ?>
                    <div>
                        <h3>
                            <a href="<?php echo esc_url($item->get_permalink()); ?>"
                               title="<?php printf(__('Posted %s', 'my-text-domain'), $item->get_date('j F Y | g:i a')); ?>">
                                <?php echo esc_html($item->get_title()); ?>
                            </a>
                        </h3>

                        <p>
                            <?php echo $item->get_description() ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php
        endif;
    }

    public function about_page_content()
    {
        ?>
        <div class="what-is-new">
            <div class="feature-section col two-col">
                <div>
                    <h3>About ClassifiedEngine</h3>

                    <p>
                        <strong>ClassifiedEngine</strong> is a WordPress theme providing all the needed features to help
                        you build up your classified ad websites. You can create a business marketplace where users sell
                        their products or service to anyone looking for the same. Also, you can add blog posts,
                        articles… and provide helpful information that supports your business. It is a great way for you
                        to get traffic, branding and income all at one.
                    </p>

                    <h3></h3>

                    <p>
                    <ol>
                        <li><a href="http://www.enginethemes.com/forums/thread-category/classifiedengine-theme/">Support
                                forum</a></li>
                        <li>
                            <a href="http://support.enginethemes.com/customer/portal/topics/606065-classifiedengine-tutorials/articles">ClassifiedEngine
                                Tutorials</a></li>
                        <li><a href="http://www.enginethemes.com/forums/thread-category/classifiedengine-ideas/">Submit
                                your ideas</a></li>
                        <li><a href="http://www.enginethemes.com/blog/">Our blog</a></li>
                    </ol>
                    </p>
                </div>
                <div class="last-feature">
                    <h3>OUR HAPPY CLIENTS</h3>

                    <p>
                        <strong>Jason Comes</strong> : "Looks absolutely amazing, the sweat blood and tears your team
                        put into this product was well worth it! Cheers to you!"
                    </p>

                    <p>
                        <strong>Devesh Sharma</strong> : "ClassifiedEngine theme is well designed and comes with all the
                        essential features that you will need to create a classified listings site with WordPress."
                    </p>

                    <p>
                        <strong>Zohdi Rizvi</strong> : "It's phenomenal, outstanding, gorgeous and intrigues a user to
                        stick on site. Surely, it contains lot of charisma in itself. Kudos to whole team :)"
                    </p>

                    <p>
                        <strong>Moritz Dawo</strong> : "Hey, this theme is looking very great and professional, thank
                        you very much for inspiring the world of business."
                    </p>

                    <p>
                        <strong>Ram Dec</strong> : "Its pleasure working with the team and I am impressed not only with
                        the theme, but also the way you do business. All the best for your future endeavors."
                    </p>
                </div>
            </div>
        </div>
    <?php
    }

    public function getPages()
    {
        return array_keys($this->pages);
    }

    public function whatisnew_page_content()
    {
        ?>
        <div class="what-is-new">
            <div class="feature-section col three-col">
                <div>
                    <h4><?php _e('Simple interface with optimized UX'); ?></h4>

                    <p><?php _e('No more headache trying to figure out how the site works, users can easily experience all the features right from the first try.'); ?></p>
                </div>
                <div>
                    <h4><?php _e('Appealing and beautiful slider in ads'); ?></h4>

                    <p><?php _e('Supported with Revolution slider, you are free to create various beautiful sliders to attract your users.'); ?></p>
                </div>
                <div class="last-feature">
                    <h4><?php _e('Easily search ads without fluster'); ?></h4>

                    <p><?php _e('With the search system, you don’t have to waste too much time struggling finding a suitable ad.'); ?></p>
                </div>
                <div>
                    <h4><?php _e('Flexible payment package plans'); ?></h4>

                    <p><?php _e('According to your preference, you can create different payment package plans to increase your online income.'); ?></p>
                </div>
                <div>
                    <h4><?php _e('Front-end controls for more convenient management'); ?></h4>

                    <p><?php _e('Mange your site right from the front-end, you can change the settings quickly.'); ?></p>
                </div>
                <div class="last-feature">
                    <h4><?php _e('Impress visitor with simple yet modern design'); ?></h4>

                    <p><?php _e('“Simple is the best”, ClassifiedEngine brings up a simple yet modern feeling, which helps you to create a professional online business.'); ?></p>
                </div>
                <div>
                    <h4><?php _e('Appear beautifully in all screen sizes'); ?></h4>

                    <p><?php _e('You don’t have to worry about the appearance of your site, from a large desktop to a small mobile, your site will appear beautifully.'); ?></p>
                </div>
                <div>
                    <h4><?php _e('Different payment gateways supported'); ?></h4>

                    <p><?php _e('More than just a simple payment gateway, users have different choices when making their transaction: Paypal, 2CheckOut, Paymill,….'); ?></p>
                </div>
                <div class="last-feature">
                    <h4><?php _e('Seller dashboard for easier ad management'); ?></h4>

                    <p><?php _e('Sellers have their own private space including all the needed information to manage all their ads'); ?></p>
                </div>
            </div>
        </div>
    <?php
    }

    public function changelog_page_content()
    {
        $file_path = get_template_directory() . '/changelog.txt';
        $filecontent = trim(file_get_contents($file_path));
        $filecontent = str_replace("\n", "<br>", $filecontent);
        $filecontent = preg_replace('/[^(\x20-\x7F)]*/', "", $filecontent);

        $file_path2 = get_template_directory() . '/readme.txt';
        $filecontent2 = trim(file_get_contents($file_path2));
        $filecontent2 = str_replace("\n", "<br>", $filecontent2);
        $filecontent2 = preg_replace('/[^(\x20-\x7F)]*/', "", $filecontent2);
        ?>
        <div class="what-is-new">
            <div class="feature-section col two-col">
                <div>
                    <h3>What was changed</h3>
                    <?php echo $filecontent2; ?>
                </div>
                <div class="last-feature">
                    <h3>File was changed</h3>
                    <?php echo $filecontent; ?>
                </div>
            </div>
        <?php
    }
}


class CE_Pointer extends VG_Pointer
{
    function __construct($excudePage = array())
    {
        $this->afteractived = array(
            'selector' => '#toplevel_page_et-overview',
            'content' => '<h3>' . __('Congratulations') . '</h3><p>' . __('Your theme has been successfully installed. Let\'s take a look at the new features!') . '</p>',
            'action_buttons' => array(
                array(
                    "text" => "Start the tour",
                    "function" => 'window.location="' . admin_url('admin.php?page=et-settings') . '";',
                )
            ),
            'position' => array('edge' => 'top', 'align' => 'center'),
        );

        $this->pointers = array(
            'et-settings' => array(
                array(
                    'selector' => '#site_title',
                    'content' => '<h3>' . __('Website Title') . '</h3><p>' . __('Insert your website title in this section.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#site_desc',
                    'content' => '<h3>' . __('Website Description') . '</h3><p>' . __('Website description is an important factor that help to boost up your SEO, therefore, please remember to describe your site’s content correctly.<br><a href="http://moz.com/learn/seo/meta-description" target="_blank">Read more</a>') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#google_analytics',
                    'content' => '<h3>' . __('Google Analytics') . '</h3><p>' . __('If you want to have detailed statistics about your visitors, you can register Google analytics and insert the code in this section.<br><a href="http://www.google.com/analytics/index.html" target="_blank">Get script</a>') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#private_key',
                    'content' => '<h3>' . __('Use Google Captcha') . '</h3><p>' . __('Enabling this will make user fill in google captcha when submitting their forms') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "Get key",
                            "function" => 'window.open(\'https://www.google.com/recaptcha/admin\', \'_blank\')',
                        ),
                        array(
                            "text" => "next",
                            "function" => 'location.hash="#section/customize-branding";setTimeout(nextPointer, 100);',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),

                ),
                array(
                    'selector' => '#website_logo_browse_button',
                    'content' => '<h3>' . __('Logo') . '</h3><p>' . __('<strong>Click here to upload your Logo</strong><br>The logo will be displayed on the top of the page, which helps to emphasize your brand name.<br>Please remember to follow our recommendation.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'top', 'align' => 'left'),
                ),
                array(
                    'selector' => '#mobile_icon_browse_button',
                    'content' => '<h3>' . __('Mobile Icon') . '</h3><p>' . __('This is your site’s icon which will appear in the mobile devices.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'location.hash="#section/setting-ad";setTimeout(nextPointer, 100);',
                        )
                    ),
                    'position' => array('edge' => 'top', 'align' => 'left'),
                ),
                //Ads
                array(
                    'selector' => 'form[data-tax=\'ad_category\'] input',
                    'content' => '<h3>' . __('Ads\' Cateogrys') . '</h3><p>' . __('You need to add categories to make your site work. After adding a category, you can create a new one by clicking the “plus” symbol (+)') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'top', 'align' => 'left'),
                ),
                array(
                    'selector' => 'form[data-tax=\'ad_location\'] input',
                    'content' => '<h3>' . __('Ads\' Localtions') . '</h3><p>' . __('Similar to how you set up the categories, you have to add locations which an ad can be assigned to. Clicking the “plus” symbol to add new location.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'top', 'align' => 'left'),
                ),
                array(
                    'selector' => '#pending_ads_point',
                    'content' => '<h3>' . __('Pending ads') . '</h3><p>' . __('If you enable this option, new posted ads won’t be displayed immediately. It’ll be consider as “pending” until you review and approve it manually.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#comment_ads_point',
                    'content' => '<h3>' . __('Comment on ads') . '</h3><p>' . __('If you enable this option, users can leave their comments below every ads.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'location.hash="#section/setting-social";setTimeout(nextPointer, 100);',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                //Social
                array(
                    'selector' => '#social_fb_point',
                    'content' => '<h3>' . __('Facebook login') . '</h3><p>' . __('If you enable this option, users can use their Facebook accounts to login to your site.To get the Application ID, please check it out <a href="http://tri.be/how-to-create-a-facebook-app-id/" target="_blank">here</a>') . '</p>',
                    'position' => array('edge' => 'right', 'align' => 'left'),
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    )
                ), array(
                    'selector' => '#social_tt_point',
                    'content' => '<h3>' . __('Twitter login') . '</h3><p>' . __('If you enable this option, users can use their Twitter accounts to login to your site.To get the Application ID, please check it out <a href="https://twittercommunity.com/t/where-is-consumer-key/1506" target="_blank">here</a>') . '</p>',
                    'position' => array('edge' => 'right', 'align' => 'left'),
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'location.hash="#section/setting-payment";setTimeout(nextPointer, 100);',
                        )
                    )
                ),
                //Payment
                array(
                    'selector' => '#payment_disable_point',
                    'content' => '<h3>' . __('Disable Payment Gateways') . '</h3><p>' . __('Enabling this will allow users to post ad for free.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ), array(
                    'selector' => '#payment_gateway_point',
                    'content' => '<h3>' . __('Payment Gateways') . '</h3><p>' . __('If you don’t enable Payment Gateway, you have to enable at least one payment gateway or the transaction process will encounter some problems.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#payment_plans_point',
                    'content' => '<h3>' . __('Payment Plans') . '</h3><p>' . __('You have to create at least one payment plan for users to post ads.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                )
            , array(
                    'selector' => '#payment_plans_form input[name=\'payment_featured\']',
                    'content' => '<h3>' . __('Featured Ad') . '</h3><p>' . __('If you tick on this option, ads posted under this plan will displayed in the Featured Ads section.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#ce_limit_free_plan',
                    'content' => '<h3>' . __('Limit Free Plan Use') . '</h3><p>' . __('Decide the number of free plans a user can use.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'location.hash="#section/setting-mail-template";setTimeout(nextPointer, 100);',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                //Email template
                array(
                    'selector' => '#email_template_point',
                    'content' => '<h3>' . __('Mail Template') . '</h3><p>' . __('You can change the mail templates in this section. We’ve already set up the default content for you, you can also changes it based on your preference.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'location.hash="#section/setting-language";setTimeout(nextPointer, 100);',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),array(
                    'selector' => '#add_language_point',
                    'content' => '<h3>' . __('Add email') . '</h3><p>' . __('Click this button to add the new language then you can translate the site. After insert the language, click “Enter”.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer()',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),array(
                    'selector' => '#base-language',
                    'content' => '<h3>' . __('Translator') . '</h3><p>' . __('Click here to select the language you want to translate. Translate each sentence then hit “Save”.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'location.hash="#section/setting-update";setTimeout(nextPointer, 100);',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),array(
                    'selector' => '#license_key',
                    'content' => '<h3>' . __('License key') . '</h3><p>' . __('Insert your license key here to update your theme.<br> You can get your license key <a href="https://www.enginethemes.com/member/member" target="_blank">here</a>') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'navToPage("' . admin_url('admin.php?page=et-sellers') . '");',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                )
            ),
            'et-sellers' => array(
                array(
                    'selector' => '#seller_point',
                    'content' => '<h3>' . __('Seller') . '</h3><p>' . __('You can get an overview about the registered sellers in this section.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#search_seller',
                    'content' => '<h3>' . __('Search seller') . '</h3><p>' . __('If you want to find a seller, insert the name in this search bar.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'navToPage("' . admin_url('admin.php?page=et-payments') . '");',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
            ),
            'et-payments' => array(
                array(
                    'selector' => '#payments_point',
                    'content' => '<h3>' . __('Payment') . '</h3><p>' . __('Similar to “Seller” page, this section allows you to see detailed information of a payment.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#et-main-left_point',
                    'content' => '<h3>' . __('Filter') . '</h3><p>' . __('Select one of these payment gateways to filter the payment.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'navToPage("' . admin_url('admin.php?page=et-api-setting') . '");',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
            ),
            'et-api-setting' => array(
                array(
                    'selector' => '#api_setting_point',
                    'content' => '<h3>' . __('Advanced Settings') . '</h3><p>' . __('You can set up some advanced settings in this page. Take a look at it then we’ll start.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#infinite_scroll_point',
                    'content' => '<h3>' . __('Infinite Scroll') . '</h3><p>' . __('If you enable this option, new ad listings will be loaded infinitely when users scroll the page.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#minify_point',
                    'content' => '<h3>' . __('Minify Script And CSS') . '</h3><p>' . __('This feature allows you to minify javascript and CSS, which will help to reduce loading time.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#ce_number_of_category',
                    'content' => '<h3>' . __('Max Number Of Ad Categories') . '</h3><p>' . __('You can set a maximum number of categories a seller can choose for an ad. Leave it blank if you don’t want to limit it.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#ce_number_of_carousel',
                    'content' => '<h3>' . __('Max Number Of Ad Images') . '</h3><p>' . __('You can set a maximum number of images a seller can upload for an ad. Please be careful, if you set up a big number, it will take a lot of space in your server’s capacity.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'nextPointer();',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
                array(
                    'selector' => '#ce_number_days_expiry',
                    'content' => '<h3>' . __('Expiration Date For Free Post Ads') . '</h3><p>' . __('You can set an expiration period for the fee posted ads in this section.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'navToPage("' . admin_url('admin.php?page=et-extensions') . '");',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
            ),
            'et-extensions' => array(
                array(
                    'selector' => '#extension_point',
                    'content' => '<h3>' . __('Extensions') . '</h3><p>' . __('If you have other requirements, please don’t forget to check out our extensions. We’ve already prepared a lot of useful extensions for you to enhance your site’s performance.') . '</p>',
                    'action_buttons' => array(
                        array(
                            "text" => "next",
                            "function" => 'navToPage("' . admin_url('admin.php?page=ce-tutorials') . '");',
                        )
                    ),
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
            ),
            'ce-tutorial' => array(
                array(
                    'selector' => '#extension_point',
                    'content' => '<h3>' . __('Finish') . '</h3><p>' . __('The tour has ended, you\'ve successfully installed your theme. You now can start your business. Thank you very much for using our products.') . '</p>',
                    'position' => array('edge' => 'right', 'align' => 'left'),
                ),
            )
        );
        $this->option_key = 'et_options';
        $this->excludePages = $excudePage;
        parent::__construct();
    }
}

class CE_Notice extends VG_Notice
{
    public function __construct()
    {
        //$this->theme_key = 'classifiedengine';
        $this->theme_key = 'classifiedengine';
        $this->new_theme_message = 'New theme update';
        parent::__construct();
    }
}

if (is_admin()) {
    $welcome = new CE_Welcome();
    $pointer = new CE_Pointer($welcome->getPages());
    $notice = new CE_Notice(); 
}