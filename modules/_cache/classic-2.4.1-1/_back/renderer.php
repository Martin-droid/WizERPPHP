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

		function menu_header($title, $no_menu, $is_index)
		{
			global $path_to_root, $SysPrefs, $db_connections;
			
			echo "<div class='fa-body'>";
			$sel_app = $_SESSION['sel_app'];
			if (!$no_menu)
			{
				$applications = $_SESSION['App']->applications;

				echo "<div class='topnav'>";
				add_access_extensions();
				foreach($applications as $app)
				{
					if ($_SESSION["wa_current_user"]->check_application_access($app))
					{
						$acc = access_string($app->name);
						$link = "$path_to_root/index.php?application=$app->id '$acc[1]";
						echo "<a ".($sel_app == $app->id ? "class='active' " : " ") . "href='$link>" . $acc[0] . "</a>\n";
					}
				}
				$cpath = company_path();
				$logo = get_company_pref('coy_logo');
				$logo_img = "$cpath/images/$logo";
				echo "<a class='right' href='#'><img src='$logo_img' style='height:28px;border:0;'></a>\n";
				echo "</div>\n"; // topnav
				// top status bar
				$rimg = "<img src='$path_to_root/themes/".user_theme()."/images/graph.png' style='width:14px;height:14px;border:0;vertical-align:middle;' alt='"._('Dashboard')."'>&nbsp;&nbsp;";
				$pimg = "<img src='$path_to_root/themes/".user_theme()."/images/preferences.png' style='width:14px;height:14px; border:0;vertical-align:middle;' alt='"._('Preferences')."'>&nbsp;&nbsp;";
				$limg = "<img src='$path_to_root/themes/".user_theme()."/images/password.png' style='width:14px;height:14px;border:0;vertical-align:middle;' alt='"._('Change Password')."'>&nbsp;&nbsp;";
				$img = "<img src='$path_to_root/themes/".user_theme()."/images/logoff.png' style='width:14px;height:14px;border:0;vertical-align:middle;' alt='"._('Logout')."'>&nbsp;&nbsp;";
				$himg = "<img src='$path_to_root/themes/".user_theme()."/images/help.png' style='width:14px;height:14px;border:0;vertical-align:middle;'' alt='"._('Help')."'>&nbsp;&nbsp;";
				echo "<div class='menu2'><div id='header'>\n";
				echo "<ul>\n";
				echo "	<li><a href='$path_to_root/admin/dashboard.php?sel_app=$sel_app'>$rimg" . _("Dashboard") . "</a></li>\n";
				echo "	<li><a href='$path_to_root/admin/display_prefs.php?'>$pimg" . _("Preferences") . "</a></li>\n";
				echo "	<li><a href='$path_to_root/admin/change_current_user_password.php?selected_id=" . $_SESSION["wa_current_user"]->username . "'>$limg" . _("Change password") . "</a></li>\n";
				if ($SysPrefs->help_base_url != null)
					echo "	<li><a target = '_blank' onclick=" .'"'."javascript:openWindow(this.href,this.target); return false;".'" '. "href='". 
						help_url()."'>$himg" . _("Help") . "</a></li>";
				echo "	<li><a href='$path_to_root/access/logout.php?'>$img" . _("Logout") . "</a></li>";
				echo "</ul>\n";
				$indicator = "$path_to_root/themes/".user_theme(). "/images/ajax-loader.gif";
				echo "<h1>" . $db_connections[user_company()]["name"] . "&nbsp;&nbsp;|&nbsp;&nbsp;" . $_SERVER['SERVER_NAME'] . "&nbsp;&nbsp;|&nbsp;&nbsp; " . $_SESSION["wa_current_user"]->name . 
					"<span style='padding-left:50px;'><img id='ajaxmark' src='$indicator' align='center' style='visibility:hidden;'></span></h1>\n";
				//echo "<h1>$SysPrefs->power_by $version<span style='padding-left:150px;'><img id='ajaxmark' src='$indicator' align='center' style='visibility:hidden;'></span></h1>\n";
				echo "</div>\n"; // header
				echo "</div>"; // menu2
				echo "<div id='main'>\n";
			}
			echo "<div class='fa-content'>\n";
			if ($no_menu)
				echo "<br />";
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
			global $version, $path_to_root, $db_connections, $Pagehelp, $Ajax, $SysPrefs;

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

			$selected_app = $waapp->get_selected_application();
			if (!$_SESSION["wa_current_user"]->check_application_access($selected_app))
				return;

			if (method_exists($selected_app, 'render_index'))
			{
				$selected_app->render_index();
				return;
			}

			echo "<table width='100%' cellpadding='0' cellspacing='0'>";
			foreach ($selected_app->modules as $module)
			{
				if (!$_SESSION["wa_current_user"]->check_module_access($module))
					continue;
				// image
				echo "<tr>";
				// values
				echo "<td class='menu_group'>";
				echo "<table border=0 width='100%'>";
				echo "<tr><td class='menu_group'>";
				echo "<span style='padding-left:18px;'>".$module->name."</span>";
				echo "</td></tr><tr>";
				echo "<td class='menu_group_items'>";

				foreach ($module->lappfunctions as $appfunction)
				{
					$img = $this->get_icon($appfunction->category);
					if ($appfunction->label == "")
						echo "&nbsp;<br />";
					elseif ($_SESSION["wa_current_user"]->can_access_page($appfunction->access)) 
					{
							echo $img.menu_link($appfunction->link, $appfunction->label)."<br />\n";
					}
					elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items())
					{
							echo $img.'<span class="inactive">'
								.access_string($appfunction->label, true)
								."</span><br />\n";
					}
				}
				echo "</td>";
				if (sizeof($module->rappfunctions) > 0)
				{
					echo "<td width='50%' class='menu_group_items'>";
					foreach ($module->rappfunctions as $appfunction)
					{
						$img = $this->get_icon($appfunction->category);
						if ($appfunction->label == "")
							echo "&nbsp;<br />";
						elseif ($_SESSION["wa_current_user"]->can_access_page($appfunction->access)) 
						{
								echo $img.menu_link($appfunction->link, $appfunction->label)."<br />\n";
						}
						elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items())
						{
								echo $img.'<span class="inactive">'
									.access_string($appfunction->label, true)
									."</span><br />\n";
						}
					}
					echo "</td>";
				}

				echo "</tr></table></td></tr>";
			}
			echo "</table>";
		}
	}
