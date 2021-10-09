<?php
/*
Plugin Name: vk-photos
Plugin URI: http://photo-family.ru/vk-photos
Description: Photo gallery from vk.com
Author: volod1n <ivan.volodin@gmail.com>
Author URI: http://photo-family.ru
Version: 1.5
*/ 

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define( 'VKP__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VKP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

$upload_dir = wp_upload_dir();

load_plugin_textdomain("vkp", false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

@require_once( VKP__PLUGIN_DIR . 'inc/class.main.php' );

add_action( 'admin_init', 'VKPPhotosRegisterSettings' );


// зарегистрируем триггер
add_filter('query_vars','vkp_add_trigger');
add_action('template_redirect', 'vkp_next_page');


//////////////////////////////////////////////////////////
// поиcк ближайшей картинки
function get_photo_size($size, $value)
{
	foreach ($value['sizes'] as $skey => $svalue) {
		if ($size == $svalue['type']) {
			return $skey;
		}
	}
	return false;
}

// перевод размеров старого api в новое

function trPictureSize($oldsize)
{
	$arSizes = array(
		'src_small' => 's',
		'src' => 'm',
		'src_big' => 'x',
		'src_xbig' => 'y',
		'src_xxbig' => 'z',
		'src_xxxbig' => 'w',
		'photo_75' => 's',
		'photo_130' => 'm',
		'photo_604' => 'x',
		'photo_807' => 'y',
		'photo_1280' => 'z',
		'photo_2560' => 'w',
	);

	return $arSizes[$oldsize];
}

////////////////////////////////////////////////////////////////////
// триггер
function vkp_add_trigger($vars) {
		$vars[] = 'vkp';
		return $vars;
}

function vkp_next_page(){
	if(isset($_POST['vkp'])){
		if($_POST['vkp']=='next-page'){
			require_once( VKP__PLUGIN_DIR . 'api/vkapi.class.php' );
			$VKP = new vkapi();      
			require_once( VKP__PLUGIN_DIR . 'inc/next-page.php' );
			exit();
		}
	}
}
////////////////////////////////////////////////////////////////////

// register_uninstall_hook( __FILE__, array('VKPhotos', 'uninstall'));
// register_activation_hook( __FILE__, array('VKPhotos', 'install') );


// инициализация
function VKPPhotosRegisterSettings(){

		// не удаление ли кеша ?
		if(is_admin() and isset($_GET['clearcache'])){
				$clearcache = explode("|",$_GET['clearcache']);
				if(isset($clearcache[0])){$clearcache_owner = ($clearcache[0]*1);}
				if(isset($clearcache[1])){$clearcache_id = ($clearcache[1]*1);}
				vkp_delete_cache($clearcache_owner,$clearcache_id);
		}

	register_setting( 'VKPPhotosSettingsGroup', 'vkpCountPhotos' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpAccaunts' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpAccaunts_type' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpEnableCaching' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpAccessToken' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpLifeTimeCaching' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpPreviewSize' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpPhotoViewSize' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpPreviewType' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpShowTitle' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpShowSignatures' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpTemplate' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpViewer' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpCalculateCache' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpShowDescription' );
	register_setting( 'VKPPhotosSettingsGroup', 'vkpMoreTitle' );




}

/////////////////////////////////////////////////////////////////////////////
// удаление кеша
function vkp_delete_cache($owner,$id){
		$upload_dir = wp_upload_dir();
		$dirForCache  = $upload_dir['basedir']."/vk-photos-cache/";
		if(file_exists($dirForCache.$owner.'/'.$id)){
				if(isset($id)){@unlink($dirForCache.$owner.'/album_'.$id.".cache");}
				@vkp_removeDir($dirForCache.$owner.'/'.$id);
		}
		wp_redirect($_SERVER['SCRIPT_NAME']."?page=vk-cache");
}

// удаление лиректории
function vkp_removeDir($path) {
		if (is_file($path)) {
				@unlink($path);
		} else {
				array_map('vkp_removeDir',glob($path.'/*')) == @rmdir($path);
		}
}

// регистрация библиотек
function vkp_scripts_register() {
		// colorbox
		wp_register_script( 'vkp_colorbox', VKP__PLUGIN_URL."js/jquery.colorbox-min.js");
		wp_register_style( 'vkp_colorbox', VKP__PLUGIN_URL."css/colorbox.css");
		// swipebox   
		wp_register_script( 'vkp_swipebox', VKP__PLUGIN_URL."js/jquery.swipebox.min.js");
		wp_register_style( 'vkp_swipebox', VKP__PLUGIN_URL."css/swipebox.css");
}

add_action('wp_enqueue_scripts', 'vkp_scripts_register');


wp_enqueue_script( 'jquery' );

if (class_exists("VKPhotos")) {
		$module_obj = new VKPhotos();
}

if (isset($module_obj)) {
		

} // if (isset($module_obj))


