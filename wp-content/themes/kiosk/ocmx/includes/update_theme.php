<?php
/*********************/
/* Add the update JS */
function ocmx_theme_update_script (){
	wp_enqueue_script( "ocmx-update", get_template_directory_uri()."/ocmx/includes/upgrade.js", array( "jquery" ) );
	wp_localize_script( "ocmx-update", "ThemeAjax", array( "ajaxurl" => admin_url( "admin-ajax.php" ) ) );
	add_action( 'wp_ajax_do_theme_upgrade', 'do_theme_upgrade' );
	add_action( 'wp_ajax_do_ocmx_upgrade', 'do_ocmx_upgrade' );
}
add_action("init", "ocmx_theme_update_script");

/********************************/
/* Add it to the OCMX Interface */
function ocmx_theme_update_options(){	
	$ocmx_tabs = array(
					array(
						  "option_header" => "Update Your Theme",
						  "use_function" => "ocmx_theme_update",
						  "function_args" => "",
						  "ul_class" => "content clearfix",
					  )
				);
	$ocmx_container = new OCMX_Container();
	$ocmx_container->load_container("Update Your Theme ", $ocmx_tabs, "");
};

/********************************************************************/
/* Add the Update Option to the admin menu, after the other options */
function add_update_page(){
	add_submenu_page("functions.php", "Update", "Update", "administrator", "ocmx-update", 'ocmx_theme_update_options');
}
add_filter("admin_menu", "add_update_page", 11);

/*******************************/
/* The Auto Update Starts here */
function ocmx_theme_update(){
    global $productid, $theme_title, $theme_version, $ocmx_version;
	$themes = get_themes();
	$current_theme = get_current_theme();
	$theme_title = $themes[$current_theme]['Title'];
	$theme_version = $themes[$current_theme]['Version'];
	$i = 2;
	$theme_updates = 0;
	$usefeed = "http://www.obox-design.com/hotfixes-$productid.xml";
	
	try {
			$string = file_get_contents($usefeed);
			$xml = @simplexml_load_string($string) or print ("no file loaded");
		}
	catch (Exception $e){
		$xml = "";
	}
	?>
      <div class="rss-widget">
             <div class="table table_content">
            <h3> Welcome to the OCMX Theme Updater</h3>
            <p>To update your theme:</p>
            <p><ol>
               <li>Backup your custom.css and any other modified theme files</li>
               <li>Note your installed version at below-right</li>
               <li>Click Install next to the update file with a version higher than your installed version, if available.</br>
               For example, if your version is 1.2.0 and there is 1.2.1 and 1.2.2 available, click install for 1.2.1, then proceed with 1.2.2 and so on.</li>
               <li>Wait for the update to finish before proceeding to other updates.</li>
               </ol></p>
            <p>If you had previously modified theme files that were overwritten by an update, you must move the changes into the new file. Do not overwrite or restore updated theme files with older versions, or the theme may break!</p>
                <?php if($xml != "") : ?>
                   <p class="sub">
                        This Theme's Latest Version: <strong><?php echo $xml->channel->version; ?></strong> | <a href="http://www.obox-design.com/hotfixes.cfm?theme=<?php echo $productid; ?>" target="_blank">Download Updates Manually</a>
                    </p>
                <?php endif; ?>
            </div>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th width="15%" align="center">Your Installed Version: <?php echo $theme_version; ?></th>
                    </tr>
                </thead>
                <tbody>
        			<?php if($xml != "") : ?>
						<?php foreach ($xml->channel->item as $xml_object => $value) : 
                            if((!get_option("theme-$productid-updates") && $theme_version < $xml->channel->version) || (get_option("theme-$productid-updates") != "" && (!in_array($value->version, get_option("theme-$productid-updates"))) )) : ?>
                                <?php $ver_id = str_replace(".", "-", $xml->channel->version, $i);
								 $update_list = explode(",", $value->updatefiles); ?>
                                <tr rel="<?php echo $value->file; ?>" id="upgrade-tr-<?php echo $ver_id; ?>">
                                    <td>
                                        <p>
                                            <strong>Update <?php echo $value->version; ?>: </strong>
                                            <?php echo $value->description; ?>
                                        </p>
                                        <p>
                                            <a href="#" rel="#upgrade-files-<?php echo $ver_id; ?>" id="upgrade-files-href-<?php echo $ver_id; ?>">Show File List</a>
                                        </p>
                                    </td>
                                    <td align="center">
                                        <p>
                                            <br />
                                            <a rel="<?php echo $value->file; ?>" id="upgrade-button-<?php echo $value->version; ?>" class="button">Install</a>
                                            <span style="float: right; display: none; margin-left: 10px;" id="upgrade-status-<?php echo $ver_id; ?>">
                                                <img src="images/loading.gif" />
                                            </span>
                                        </p>
                                    </td>
                                </tr>
                                <tbody id="upgrade-files-<?php echo $ver_id; ?>" class="no_display">
                                    <tr>
                                        <td colspan="2">
                                            <strong>Updated Files:</strong>                        
                                            <p><?php echo str_replace(", ", "<br />", $value->updatefiles); ?></p>
                                        </td>
                                    </tr>
                                </tbody>
                        <?php $theme_updates++;
							endif;
							if($theme_updates == 0) : ?>
                         	<tr><td colspan="2"><p>You have the latest version of this theme.</p></td></tr>
						<?php endif;
                        endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="2"><p>There are no updates available for this theme.</p></td></tr>
       				<?php endif; ?>
                </tbody>
            </table>
	        <br />
        <table class="widefat">
        	<thead>
                <tr>
                    <th>OCMX Updates</th>
                </tr>
            </thead>
            <tbody>
				<?php $ocmx_feed = "http://www.obox-design.com/hotfixes-ocmx.xml";
                $string = file_get_contents($ocmx_feed);
                $ocmx_xml = @simplexml_load_string($string) or print ("no file loaded");
				$ocmx_updates = 0;
                foreach ($ocmx_xml->channel->item as $xml_object => $value) : 
                    $ver_id = str_replace(".", "-", $value->version, $i); ?>
					<?php if(str_replace(".", "", $value->version, $i) > str_replace(".", "", $ocmx_version, $i)) : ?>
                        <tr rel="<?php echo $value->file; ?>" id="upgrade-tr-<?php echo $ver_id; ?>">
                            <td>
                                <p>
                                    <strong>Update <?php echo $value->version; ?>: </strong>
                                    <?php echo $value->description; ?>
                                </p>
                            </td>
                        </tr>
                    <?php $ocmx_updates++;
                    endif;
               	endforeach;  						 
				if($ocmx_updates == 0) : ?>
					<tr><td><p>You have the latest version of OCMX</p></td></tr>
				<?php endif;?>
            </tbody>
        </table>
        <?php if($ocmx_updates !== 0) : ?>
			<p align="right">
            	<a rel="<?php echo $value->file; ?>" id="upgrade-ocmx-button-<?php echo $ocmx_xml->channel->version; ?>" class="button">Update to OCMX <?php echo $ocmx_xml->channel->version; ?></a>
                <span style="float: right; display: none; margin-left: 10px;" id="upgrade-status-<?php echo str_replace(".", "-", $ocmx_xml->channel->version, $i); ?>">
                    <img src="images/loading.gif" />
                </span>
            </p>
		<?php  endif; ?>
</div>
<?php 
} 
add_action("ocmx_theme_update", "ocmx_theme_update");

function do_ocmx_upgrade(){
	global $productid;
	$theme_upgrade = new obox_theme_update();
	$destination = get_template_directory();
	$zipfile = $_GET["zipfile"];
	$version = $_GET["version"];
	
	$package = "http://www.obox-design.com/ocmx_hotfix.cfm?ver=$version";
	$defaults = array( 	'package' => $package, //Please always pass this.
						'destination' => $destination, //And this
						'clear_destination' => false,
						'clear_working' => true,
						'is_multi' => false,
						'hook_extra' => array() //Pass any extra $hook_extra args here, this will be passed to any hooked filters.
					);
	$show_progress = $theme_upgrade->run($defaults);
	
	if ( is_wp_error($show_progress) ) :
   		echo $show_progress->get_error_message();
	endif;
	
	die("");
};
function do_theme_upgrade(){
	global $productid;
	$theme_upgrade = new obox_theme_update();
	$destination = TEMPLATEPATH;
	$zipfile = $_GET["zipfile"];
	$version = $_GET["version"];
	
	$package = "http://www.obox-design.com/hotfixes/new/$zipfile";
	$defaults = array( 	'package' => $package, //Please always pass this.
						'destination' => $destination, //And this
						'clear_destination' => false,
						'clear_working' => true,
						'is_multi' => false,
						'hook_extra' => array() //Pass any extra $hook_extra args here, this will be passed to any hooked filters.
					);
	$show_progress = $theme_upgrade->run($defaults);
	
	if ( is_wp_error($show_progress) ) :
   		echo $show_progress->get_error_message();
	endif;
	
	if(!get_option("theme-$productid-updates")) :
		$theme_versions = array();
		$theme_versions[] = $version;
	else :
		$theme_versions = get_option("theme-$productid-updates");
		$theme_versions[] = $version;
	endif;
	
	if(!get_option("theme-$productid-updates") || !in_array($value->version, get_option("theme-$productid-updates"))):
		update_option("theme-$productid-updates", $theme_versions);
	endif;
	
	die("");
}; ?>