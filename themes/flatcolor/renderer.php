<?php
/**********************************************************************
	Copyright (C) FrontAccounting Team.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
	See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
// Author: Joe Hunt, 01/06/2019. Release 2.4.

	class renderer
	{
		function get_icon($category)
		{
			global	$path_to_root, $SysPrefs;

			if ($SysPrefs->show_menu_category_icons)
				$img = $category == '' ? 'right.gif' : $category.'.png';
			else	
				$img = 'right.gif';
			return "<img src='$path_to_root/themes/".user_theme()."/images/$img' style='vertical-align:middle;' border='0'>&nbsp;&nbsp;";
		}

		function wa_header()
		{
			page(_($help_context = "Main Menu"), false, true);
		}

		function wa_footer()
		{
			end_page(false, true);
		}
		function shortcut($url, $label) 
		{
			echo "<li>";
			echo menu_link($url, $label);
			echo "</li>";
		}
		function menu_header($title, $no_menu, $is_index)
		{
			global $path_to_root, $SysPrefs, $version, $db_connections, $installed_extensions;

			echo "<div class='fa-body'>";
			$sel_app = $_SESSION['sel_app'];
			if (!$no_menu)
			{
				$applications = $_SESSION['App']->applications;

				echo "<div class='topnav'>";
				add_access_extensions();
				$i = 0;
				$cur_app = array();
				foreach($applications as $app)
				{
					if ($_SESSION["wa_current_user"]->check_application_access($app))
					{
						if ($sel_app == $app->id)
							$cur_app = $app;	
						$acc = access_string($app->name);
						$link = "$path_to_root/index.php?application=$app->id '$acc[1]";
						echo "<a ".($sel_app == $app->id ? "class='active' " : " ") . "href='$link>" . $acc[0] . "</a>\n";
					}
				}
				$cpath = company_path();
				$logo = get_company_pref('coy_logo');
				$logo_img = "$cpath/images/$logo";
				echo "<a class='right' href='#'><img src='$logo_img' style='height:28px;'></a>\n";
				echo "</div>\n"; // topnav
				$applications = $_SESSION['App']->applications;
				$local_path_to_root = $path_to_root;
				$pimg = "<img src='$local_path_to_root/themes/".user_theme()."/images/preferences.png' style='width:14px;height:14px;border:0;vertical-align:middle;padding-bottom:3px;' alt='"._('Preferences')."'>&nbsp;&nbsp;";
				$limg = "<img src='$local_path_to_root/themes/".user_theme()."/images/password.png' style='width:14px;height:14px;border:0;vertical-align:middle;padding-bottom:3px;' alt='"._('Change Password')."'>&nbsp;&nbsp;";
				$img = "<img src='$local_path_to_root/themes/".user_theme()."/images/logoff.png' style='width:14px;height:14px;border:0;vertical-align:middle;padding-bottom:3px;' alt='"._('Logout')."'>&nbsp;&nbsp;";
				$himg = "<img src='$local_path_to_root/themes/".user_theme()."/images/help.png' style='width:14px;height:14px;border:0;vertical-align:middle;padding-bottom:3px;' alt='"._('Help')."'>&nbsp;&nbsp;";
				echo "<div id='menu2'><div id='header'>\n";
				echo "<ul>\n";
				echo "	<li><a href='$local_path_to_root/admin/display_prefs.php?'>$pimg" . _("Preferences") . "</a></li>\n";
				echo "	<li><a href='$local_path_to_root/admin/change_current_user_password.php?selected_id=" . $_SESSION["wa_current_user"]->username . "'>$limg" . _("Change password") . "</a></li>\n";
				if ($SysPrefs->help_base_url != null)
					echo "	<li><a target = '_blank' onclick=" .'"'."javascript:openWindow(this.href,this.target); return false;".'" '. "href='". 
						help_url()."'>$himg" . _("Help") . "</a></li>";
				echo "	<li><a href='$path_to_root/access/logout.php?'>$img" . _("Logout") . "</a></li>";
				echo "</ul>\n";
				echo "</div>"; // header
				echo "<ul>\n"; // menu2

				$app = $cur_app;
				if ($app->id == "system")
					$imgs2 = array("menu_settings.png", "menu_settings.png", "menu_maintenance.png", "menu_system.png", "menu_system.png");
				else	
					$imgs2 = array("menu_transaction.png", "menu_inquiry.png", "menu_maintenance.png", "menu_money.png", "menu_transaction.png");
				$j = -1;
				foreach ($app->modules as $module)
				{
					$j++;
					if (!$_SESSION["wa_current_user"]->check_module_access($module))
						continue;

					$img_src = "<img style='vertical-align:middle;' src='$path_to_root/themes/".user_theme()."/images/".$imgs2[$j]."' width='14' height='14' border='0' />";
					
					$apps = array();
					$i = $k = 0;
					foreach ($module->lappfunctions as $appfunction)
						$apps[] = $appfunction;
					$i = count($apps);
					foreach ($module->rappfunctions as $appfunction)
						$apps[] = $appfunction;
					$count = 0;	
					foreach ($apps as $value)
					{
						if (!empty($value->label))
							$count++;
					}
					$circle = "<span class='circle'>$count</span>";
					// Remove the number circle set $circle = ""
					echo "<li><a class='active' href='#'>$img_src&nbsp;&nbsp;$module->name $circle</a>\n";

					echo "<ul>\n";
					$application = array();	
					
					foreach ($apps as $application)	
					{
						if ($i == $k)
							$line = "class='line'";
						else
							$line = "";
						$k++;
						$img = $this->get_icon($application->category);
						$lnk = access_string($application->label);
						if ($_SESSION["wa_current_user"]->can_access_page($application->access))
						{
							if ($application->label != "")
							{
								echo "<li $line><a href='$path_to_root/$application->link'>$img{$lnk[0]}</a></li>\n";
							}
						}
						elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items())
							echo "<li $line><a class='inactive'>$img{$lnk[0]}</a></li>\n";
					}
					echo "</ul>\n";
					echo "</li>\n";
				}
				echo "</ul>\n";
				$indicator = "$path_to_root/themes/".user_theme(). "/images/ajax-loader.gif";
				echo "<span style='padding-left:50px;'><img id='ajaxmark' src='$indicator' align='center' style='visibility:hidden;'></span>\n";
				echo "</div>\n"; // menu2
				echo "<div id='main'>\n";
			}
			echo "<div class='fa-content'>\n";
			if ($no_menu)
				echo "<br>";
			elseif ($title && !$no_menu && !$is_index)
			{
				echo "<center><table id='title'><tr><td width='100%' class='titletext'>$title</td>"
				."<td align=right>"
				.(user_hints() ? "<span id='hints'></span>" : '')
				."</td>"
				."</tr></table></center>";
			}
		}

		function menu_footer($no_menu, $is_index)
		{
			global $path_to_root, $SysPrefs, $version, $db_connections, $Ajax, $Pagehelp;
			include_once($path_to_root . "/includes/date_functions.inc");

			echo "</div>\n"; // fa-content
			if (!$no_menu)
			{
				echo "</div>\n"; // main
				echo "<div class='fa-footer'>\n";
				if (isset($_SESSION['wa_current_user']))
				{
					$phelp = implode('; ', $Pagehelp);
					$Ajax->addUpdate(true, 'hotkeyshelp', $phelp);
					echo "<span id='hotkeyshelp' style='float:right;'>".$phelp."</span>";
					echo "<span class='power'><a target='_blank' href='$SysPrefs->power_url'>&nbsp;&nbsp;$SysPrefs->power_by $version</a></span>\n";
					echo "<span class='date'>".Today() . "&nbsp;" . Now()."</span>\n";
					echo "<span class='date'>" . $db_connections[$_SESSION["wa_current_user"]->company]["name"] . "</span>\n";
					echo "<span class='date'>" . $_SERVER['SERVER_NAME'] . "</span>\n";
					echo "<span class='date'>" . $_SESSION["wa_current_user"]->name . "</span>\n";
					echo "<span class='date'>" . _("Theme:") . " " . user_theme() . "</span>\n";
					echo "<span class='date'>".show_users_online()."</span>\n";
				}
				echo "</div>\n"; // footer
			}
			echo "</div>\n"; // fa-body
		}

		function display_applications(&$waapp)
		{
			global $path_to_root;

			$sel = $waapp->get_selected_application();
			meta_forward("$path_to_root/admin/dashboard.php", "sel_app=$sel->id");	
			end_page();
			exit;
		}
	}