<?php
/**
 * Project : classifiedengine
 * User: thuytien
 * Date: 11/27/2014
 * Time: 9:37 AM
 */

if (class_exists('WooCommerce')):
    class WC_Admin_Report_Integrate extends WC_Admin_Report
    {
        public function get_order_report_data($args = array())
        {
            global $wpdb;

            $default_args = array(
                'data' => array(),
                'where' => array(),
                'where_meta' => array(),
                'query_type' => 'get_row',
                'group_by' => '',
                'order_by' => '',
                'limit' => '',
                'filter_range' => false,
                'nocache' => true,
                'debug' => false,
                'order_types' => wc_get_order_types('reports'),
                'order_status' => array('pending', 'publish', 'draft'),
                'parent_order_status' => false,
            );
            $args = apply_filters('ce_reports_get_order_report_data_args', $args);
            $args = wp_parse_args($args, $default_args);

            extract($args);

            if (empty($data)) {
                return '';
            }

            $order_status = apply_filters('ce_reports_order_statuses', $order_status);

            $query = array();
            $select = array();

            foreach ($data as $key => $value) {
                $distinct = '';

                if (isset($value['distinct'])) {
                    $distinct = 'DISTINCT';
                }

                if ($value['type'] == 'meta') {
                    $get_key = "meta_{$key}.meta_value";
                } elseif ($value['type'] == 'post_data') {
                    $get_key = "posts.{$key}";
                } elseif ($value['type'] == 'order_item_meta') {
                    $get_key = "order_item_meta_{$key}.meta_value";
                } elseif ($value['type'] == 'order_item') {
                    $get_key = "order_items.{$key}";
                } else {
                    continue;
                }

                if ($value['function']) {
                    $get = "{$value['function']}({$distinct} {$get_key})";
                } else {
                    $get = "{$distinct} {$get_key}";
                }

                $select[] = "{$get} as {$value['name']}";
            }

            $query['select'] = "SELECT " . implode(',', $select);
            $query['from'] = "FROM {$wpdb->posts} AS posts";

            // Joins
            $joins = array();

            foreach ($data as $key => $value) {

                if ($value['type'] == 'meta') {

                    $joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";

                } elseif ($value['type'] == 'order_item_meta') {

                    $joins["order_items"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";
                    $joins["order_item_meta_{$key}"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON order_items.order_item_id = order_item_meta_{$key}.order_item_id";

                } elseif ($value['type'] == 'order_item') {

                    $joins["order_items"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";

                }
            }

            if (!empty($where_meta)) {

                foreach ($where_meta as $value) {

                    if (!is_array($value)) {
                        continue;
                    }

                    $key = is_array($value['meta_key']) ? $value['meta_key'][0] . '_array' : $value['meta_key'];

                    if (isset($value['type']) && $value['type'] == 'order_item_meta') {

                        $joins["order_items"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";
                        $joins["order_item_meta_{$key}"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON order_items.order_item_id = order_item_meta_{$key}.order_item_id";

                    } else {
                        // If we have a where clause for meta, join the postmeta table
                        $joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
                    }
                }
            }

            if (!empty($parent_order_status)) {
                $joins["parent"] = "LEFT JOIN {$wpdb->posts} AS parent ON posts.post_parent = parent.ID";
            }

            $query['join'] = implode(' ', $joins);

            $query['where'] = "
			WHERE 	posts.post_type 	IN ( '" . implode("','", $order_types) . "' )
			";

            if (!empty($order_status)) {
                $query['where'] .= "
				AND 	posts.post_status 	IN ( '" . implode("','", $order_status) . "')
			";
            }

            if (!empty($parent_order_status)) {
                $query['where'] .= "
				AND 	parent.post_status 	IN ( '" . implode("','", $parent_order_status) . "')
			";
            }

            if ($filter_range) {

                $query['where'] .= "
				AND 	posts.post_date >= '" . date('Y-m-d', $this->start_date) . "'
				AND 	posts.post_date < '" . date('Y-m-d', strtotime('+1 DAY', $this->end_date)) . "'
			";
            }

            foreach ($data as $key => $value) {

                if ($value['type'] == 'meta') {

                    $query['where'] .= " AND meta_{$key}.meta_key = '{$key}'";

                } elseif ($value['type'] == 'order_item_meta') {

                    $query['where'] .= " AND order_items.order_item_type = '{$value['order_item_type']}'";
                    $query['where'] .= " AND order_item_meta_{$key}.meta_key = '{$key}'";

                }
            }

            if (!empty($where_meta)) {

                $relation = isset($where_meta['relation']) ? $where_meta['relation'] : 'AND';

                $query['where'] .= " AND (";

                foreach ($where_meta as $index => $value) {

                    if (!is_array($value)) {
                        continue;
                    }

                    $key = is_array($value['meta_key']) ? $value['meta_key'][0] . '_array' : $value['meta_key'];

                    if (strtolower($value['operator']) == 'in') {

                        if (is_array($value['meta_value'])) {
                            $value['meta_value'] = implode("','", $value['meta_value']);
                        }

                        if (!empty($value['meta_value'])) {
                            $where_value = "IN ('{$value['meta_value']}')";
                        }
                    } else {
                        $where_value = "{$value['operator']} '{$value['meta_value']}'";
                    }

                    if (!empty($where_value)) {
                        if ($index > 0) {
                            $query['where'] .= ' ' . $relation;
                        }

                        if (isset($value['type']) && $value['type'] == 'order_item_meta') {

                            if (is_array($value['meta_key'])) {
                                $query['where'] .= " ( order_item_meta_{$key}.meta_key   IN ('" . implode("','", $value['meta_key']) . "')";
                            } else {
                                $query['where'] .= " ( order_item_meta_{$key}.meta_key   = '{$value['meta_key']}'";
                            }

                            $query['where'] .= " AND order_item_meta_{$key}.meta_value {$where_value} )";
                        } else {

                            if (is_array($value['meta_key'])) {
                                $query['where'] .= " ( meta_{$key}.meta_key   IN ('" . implode("','", $value['meta_key']) . "')";
                            } else {
                                $query['where'] .= " ( meta_{$key}.meta_key   = '{$value['meta_key']}'";
                            }

                            $query['where'] .= " AND meta_{$key}.meta_value {$where_value} )";
                        }
                    }
                }

                $query['where'] .= ")";
            }

            if (!empty($where)) {

                foreach ($where as $value) {

                    if (strtolower($value['operator']) == 'in') {

                        if (is_array($value['value'])) {
                            $value['value'] = implode("','", $value['value']);
                        }

                        if (!empty($value['value'])) {
                            $where_value = "IN ('{$value['value']}')";
                        }
                    } else {
                        $where_value = "{$value['operator']} '{$value['value']}'";
                    }

                    if (!empty($where_value))
                        $query['where'] .= " AND {$value['key']} {$where_value}";
                }
            }

            if ($group_by) {
                $query['group_by'] = "GROUP BY {$group_by}";
            }

            if ($order_by) {
                $query['order_by'] = "ORDER BY {$order_by}";
            }

            if ($limit) {
                $query['limit'] = "LIMIT {$limit}";
            }

            $query = apply_filters('woocommerce_reports_get_order_report_query', $query);
            $query = implode(' ', $query);
            $query_hash = md5($query_type . $query);
            $cached_results = get_transient(strtolower(get_class($this)));

            if ($debug) {
                echo '<pre>';
                print_r($query);
                echo '</pre>';
            }

            if ($debug || $nocache || false === $cached_results || !isset($cached_results[$query_hash])) {
                $cached_results[$query_hash] = apply_filters('woocommerce_reports_get_order_report_data', $wpdb->$query_type($query), $data);
                set_transient(strtolower(get_class($this)), $cached_results, DAY_IN_SECONDS);
            }

            $result = $cached_results[$query_hash];
            return $result;
        }

        public function get_currency_tooltip()
        {
            switch (get_option('woocommerce_currency_pos')) {
                case 'right':
                    $currency_tooltip = 'append_tooltip: "' . get_woocommerce_currency_symbol() . '"';
                    break;
                case 'right_space':
                    $currency_tooltip = 'append_tooltip: "&nbsp;' . get_woocommerce_currency_symbol() . '"';
                    break;
                case 'left':
                    $currency_tooltip = 'prepend_tooltip: "' . get_woocommerce_currency_symbol() . '"';
                    break;
                case 'left_space':
                default:
                    $currency_tooltip = 'prepend_tooltip: "' . get_woocommerce_currency_symbol() . '&nbsp;"';
                    break;
            }

            return $currency_tooltip;
        }
    }
endif;