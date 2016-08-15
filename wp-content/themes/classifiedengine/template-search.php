<?php
global  $wp_query;
$location_list    = ce_get_locations();
$ce_categories    = ce_get_categories();

/**
 * category slug
*/
$c_str  =   ET_AdCatergory::slug();
/**
 * location string
*/
$l_str  =   ET_AdLocation::slug();

?>
<div class="header-filter" id="header-filter">
   <div class="main-center container" id="search_form">
        <form action="<?php echo home_url( '/' ); ?>" method="GET" id="search-form">
            <div class="row">
                <div class="col-md-3">
                    <?php if(!empty($location_list)) { ?>
                    <div class="controls select-style">
                        <!-- begin choosen lcoation !-->
                        <?php
                            $location     = $all_local =  __('All Locations', ET_DOMAIN);

                            $local_cookie = isset($_COOKIE['et_location']) ? $_COOKIE['et_location'] :'';
                            $ad_location  = ( get_query_var( $l_str ) ) ? get_query_var( $l_str ) : $local_cookie ;



                            /**
                             * process to catch queried location
                            */
                            $ad_location  = ( get_query_var( $l_str ) ) ? get_query_var( $l_str ) : $local_cookie ;
                            if(is_tax( 'ad_location' )) {
                                $ad_location    =   $wp_query->get_queried_object()->slug;
                            }

                            if(!empty($ad_location) ){
                                $cur_local  = get_term_by('slug',urldecode($ad_location),'ad_location');
                                if(isset($cur_local->name))
                                    $location   = $cur_local->name;
                            }
                            /**
                             * process catch queried cat
                            */
                            $ad_category  = (get_query_var( $c_str ) ) ? get_query_var( $c_str ) : '' ;

                            if( is_tax(CE_AD_CAT) ) {
                                $ad_category    =   $wp_query->get_queried_object()->slug;
                            }

                            $locations     = ce_get_locations();
                            $space  = '';
                            $parent = 0;
                        ?>
                        <select name="location" class="filter-location" id="search_location">
                            <option class="<?php echo $space; ?>" value=""> <?php echo $all_local; ?></option>
                            <?php

                            foreach ($locations as $k => $loc) {
                                $tem_parent =   $loc->parent;
                                if( $tem_parent != $parent )  $space = 'sub';
                                if( $tem_parent == 0 ) $space   =   '';
                                $parent =   $tem_parent;
                            ?>
                                <option <?php selected( $ad_location, $loc->slug, true ); ?> class="<?php echo $space; ?>" value="<?php echo $loc->slug ?>" data-text-alter='<?php echo $loc->name; ?>' ><?php echo $loc->name; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <!-- end search choosen location !-->
                    <?php } ?>
                </div>
                <div class="col-md-9 search search-home ">
                    <div class="search-inner" >
                        <input type="text" placeholder="<?php _e('Search ...',ET_DOMAIN);?>" value="<?php echo sanitize_text_field (get_query_var('s'));?>" name="s" id="s" />
                        <!-- <input type="hidden" id="location" value="<?php echo $ad_location ?>" name="<?php echo $l_str;?>" /> -->
                        <input type="hidden" id="ad_cat" value="<?php echo $ad_category ?>" name="<?php echo $c_str;?>" />
                        <?php
                        global  $cat_name;
                        $cat_name =  $text_default = __('All Categories', ET_DOMAIN);


                        if(!empty($ce_categories)) {

                            if(!empty($ad_category)){
                                $cat      =   get_term_by('slug',urldecode($ad_category),CE_AD_CAT);
                                if(isset($cat->name))
                                    $cat_name = $cat->name;
                            }

                        ?>

                        <div class="btn-group show-box-category">
                            <button class="btn button-show show-cat">
                                <span class="select"><?php echo $cat_name; ?></span><i class="fa fa-arrow-down"></i>
                            </button>
                            <div class="category-search-dropdown">
                                <div class="scrollbar2">
                                   <div class="scrollbar">
                                        <div class="track">
                                            <div class="thumb">
                                                <div class="end">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="viewport">
                                        <div class="overview">
                                            <ul class="search-category">
                                                <?php if(empty($ad_category)) { ?>
                                                    <li class="check-all" data-slug=''  >
                                                        <span class="icon" data-icon="3"></span>
                                                        <?php echo $cat_name; ?>
                                                    </li>
                                                <?php } else { ?>
                                                    <li data-slug=''  >
                                                        <?php echo $text_default; ?>
                                                    </li>
                                                <?php } ?>
                                                <?php foreach ($ce_categories as $key => $sub_cat) {
                                                    if($sub_cat->parent == 0) {
                                                        if( empty($ad_category) ) {
                                                     ?>
                                                        <li data-slug='<?php echo $sub_cat->slug ?>' ><span class="text"><?php echo $sub_cat->name ?></span></li>
                                                    <?php } else { ?>
                                                        <li data-slug='<?php echo $sub_cat->slug ?>' <?php if($sub_cat->term_id == $cat->term_id || $sub_cat->parent == $cat->term_id) echo 'class="check-all"'; ?> >
                                                            <?php if($sub_cat->term_id == $cat->term_id || $sub_cat->parent == $cat->term_id) echo '<span class="icon" data-icon="3"></span>'; ?>
                                                            <span class="text"><?php echo $sub_cat->name ?></span>
                                                        </li>
                                                    <?php }
                                                    }
                                                } ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="btn btn-primary  button-search search-top">
                        <i class="fa fa-search"></i>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div><!--search header !-->
