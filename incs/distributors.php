<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Joe Buckle
 * Date: 28/01/13
 * Time: 09:41
 */

function distributors( $atts, $content=null ) {

    wp_reset_query();

    ?>

        <div id="map_canvas"></div>

        <div class="map_listings">

            <?php

            global $wpdb;

            $query = $wpdb->get_results( "SELECT * FROM wp_locations" );



            foreach($query as $item) {

                $array[] = array(

                   $item->country => array(

                       'name' => $item->name,

                       'address' => $item->address,

                       'email' => $item->email,

                       'telephone' => $item->telephone,

                   )

                );

            }

            foreach($array as $item) {

                $key = key($item);

                $cat = current($item);



                if(!isset($result[$key])) {

                    $result[$key] = array();

                }

                $result[$key][] = $cat;

            }



            foreach($result as $key => $cats) {

                echo '<div class="block">';

                echo '<h2><strong>'.$key.'</strong></h2>';

                foreach($cats as $cat) {

                    if($cat['name']) {

                        echo '<h3>'.$cat['name'].'</h3>';

                    }

                    if($cat['telephone']) {

                        echo '<p><strong>Phone:&nbsp;</strong>'.$cat['telephone'].'</p>';

                    }

                    if($cat['email']) {

                        echo '<p><strong>Email:&nbsp;</strong>'.$cat['email'].'</p>';

                    }

                    if($cat['address']) {

                        echo '<p><strong>Address:<br /></strong>'.str_replace(',', '<br />', $cat['address']).'</p>';

                    }

                }

                echo "</div>";

            }



            ?>

        </div>

        <?php

        wp_register_script( 'sensor', 'http://maps.google.com/maps/api/js?sensor=true', array('jquery'),null,true );

        wp_register_script( 'clusterer', get_template_directory_uri() . '/js/maps/markerclusterer.min.js', array('jquery'),null,true );

        wp_register_script( 'jquery-ui-map',  get_template_directory_uri() . '/js/maps/jquery.ui.map.full.min.js', array('jquery'),null,true );





        wp_enqueue_script( 'sensor' );

        wp_enqueue_script( 'clusterer' );

        wp_enqueue_script( 'jquery-ui-map' );



        function dribox_maps() {

            global $wpdb;

            $details = $wpdb->get_results( "SELECT * FROM wp_locations" );



            foreach($details as $item) {

                $coords[] = array(

                    'country'=> $item->country,

                    'address'=> $item->address,

                    'name'=> $item->name,

                    'email' => $item->email,

                    'telephone' => $item->telephone,

                    'coords' => $item->coords

                );

            }





            ?>

            <script type="text/javascript">

                jQuery(document).ready(function() {

                    jQuery('#map_canvas').gmap({'zoom': 4, 'center' : '49.2255742,5.4468273'}).bind('init', function(evt, map) {

                        var bounds = map.getBounds();

                        var southWest = bounds.getSouthWest();

                        var northEast = bounds.getNorthEast();

                        var lngSpan = northEast.lng() - southWest.lng();

                        var latSpan = northEast.lat() - southWest.lat();

                        var self = this;

                        <?php foreach($coords as $coord) { ?>

                            <?php if($coord['coords']!=",")  { ?>

                                jQuery(self).gmap('addMarker', {

                                    'position': new google.maps.LatLng(<?php echo $coord['coords'] ?>),

                                    'icon': '<?php echo get_template_directory_uri(); ?>/img/MAP-pin2.png'

                                }).mouseover(function() {

                                    jQuery(self).gmap('openInfoWindow', { content : '<h3><?php echo $coord['name']; ?></h3><?php echo str_replace(array("\r","\n"), "<p></p>", $coord['address']); ?><?php if($coord['email']) { ?><h4>EMAIL: <a href="mailto: <?php echo $coord['email']; ?>"><?php echo $coord['email']; ?></a></h4><?php  } // end email ?><?php if($coord['telephone']) { ?><h4>TELEPHONE:<?php echo $coord['telephone']; ?></h4><?php } // end telephone ?>' }, this);

                                });

                            <?php  } ?>

                    <?php

                        } ?>

                        jQuery(self).gmap('set', 'MarkerClusterer', new MarkerClusterer(map, jQuery(this).gmap('get', 'markers')));

                    });

                });

            </script>

            <?php

        }

        add_action('wp_footer', 'dribox_maps' ,30);

    }



function distributors_init($atts, $content=null) {

    ob_start();

    distributors($atts, $content=null);

    $output=ob_get_contents();

    ob_end_clean();

    return $output;

}

add_shortcode( 'distributors', 'distributors_init' );



// Admin Bit

add_action( 'admin_menu', 'distributors_admin' );



function distributors_admin() {

    add_menu_page( 'Distributor Locations', 'Distributor Locations', 'manage_options', 'dist-loc', 'distributors_admin_options' );

}



function distributors_admin_options() {

    if ( !current_user_can( 'manage_options' ) )  {

        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

    }

    global $wpdb;

    if($_POST['update_loc']) {

        $wpdb->update('wp_locations',

	      array(

            "country" => $_POST['country'],

            "name" => $_POST['name'],

	        "address" => stripslashes($_POST['address']),

	        "email" => $_POST['email'],

            "telephone" => $_POST['telephone'],

            "coords" => toCoordinates($_POST['address'])),

            array("ID" => $_POST['ID'])

        );

    }

    if($_POST['remove_loc']) {

        $wpdb->query("DELETE FROM wp_locations WHERE ID = ".$_POST['ID']."");

    }

    if($_POST['save_loc']) {

        $wpdb->insert(

            'wp_locations',

            array(

                'country' => $_POST['country'],

                'name' => $_POST['name'],

                'address' => $_POST['address'],

                'email' => $_POST['email'],

                'telephone' => $_POST['telephone'],

                'coords' => toCoordinates($_POST['address'])

            ),

            array(

                '%s',

                '%s',

                '%s',

                '%s',

                '%s',

                '%s',

            )

        );

    }

    $details = $wpdb->get_results( "SELECT * FROM wp_locations" );

    echo '<table class="widefat distribut">';

    echo '<tr>';

    echo '<thead>';

    echo '<th>Unique ID</th><th>Name</th><th>Country</th><th>Address</th><th>Email</th><th>Telephone</th><th>Remove/Save</th>';

    echo '</thead>';

    echo '<tbody>';

    foreach($details as $item) {

        echo "<form method='post' action='' class='dist'>";

        echo "<tr>";

        echo "<td><input type='hidden' name='ID' value='".$item->ID."' />".$item->ID."</td>";

        echo "<td valign='top'><input class='dist' type='text' name='name' value='".$item->name."' /></td>";

        echo "<td valign='top'><input class='dist' type='text' name='country' value='".$item->country."' /></td>";

        echo "<td><textarea class='dist ";

        $coords = toCoordinates($item->address);

        echo "' name='address'/>".$item->address."</textarea></td>";

        echo "<td><input class='dist' type='text' name='email' value='".$item->email."' /></td>";

        echo "<td><input class='dist' type='text' name='telephone' value='".$item->telephone."' /></td>";

        echo "<td>

        <input class='button-primary' type='submit' name='update_loc' value='update' />

        <input class='button-secondary' type='submit' name='remove_loc' value='remove' />

        </td>";

        echo "</tr>";

        echo "</form>";

    }

    echo "</tbody>";

    echo "<tfoot>";

    echo "<form method='post' action='' class='dist'>";

    echo "<tr>";

    echo "<td>ADD NEW</td>";

    echo "<td><input class='dist' type='text' name='name' />";

    echo "<td><input class='dist' type='text' name='country' />";

    echo "<td><textarea class='dist' name='address'/></textarea></td>";

    echo "<td><input class='dist' type='text' name='email'  /></td>";

    echo "<td><input class='dist' type='text' name='telephone' /></td>";

    echo "<td><input class='button-primary' type='submit' name='save_loc' value='save' /></td>";

    echo "</tr>";

    echo "</form>";

    echo "</tfoot>";

    echo '</table>';

}



function custom_colors() {

    echo '<style type="text/css">

            input.dist, textarea.dist {

                padding:10px;


                width:100%;

            }

            textarea.dist {

                height:150px;

            }

            table.distribut {

                margin:20px 0px;

            }

            textarea.dist.not-valid {

                background:pink;

            }

         </style>';

}



add_action('admin_head', 'custom_colors');