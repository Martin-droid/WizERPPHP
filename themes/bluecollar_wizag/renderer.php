<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
// Author: Joe Hunt, 11/15/2015

	class renderer
	{
		function wa_get_apps($title, $applications, $sel_app)
		{
			foreach($applications as $app)
			{
				foreach ($app->modules as $module)
				{
					$apps = array();
					foreach ($module->lappfunctions as $appfunction)
						$apps[] = $appfunction;
					foreach ($module->rappfunctions as $appfunction)
						$apps[] = $appfunction;
					$application = array();	
					foreach ($apps as $application)	
					{
						$lnk = $_SERVER['REQUEST_URI'];
						$pos = strpos($application->link, "/");
						if ($pos > 0)
						{
							$str = substr($application->link, 0, $pos + 1);
							$app_lnk = substr($application->link, $pos + 1);
							$pos = strpos($app_lnk, "?");
							if ($pos == false)
								$pos = strlen($app_lnk);
							$app_lnk = substr($app_lnk, 0, $pos);
							$pos = strrpos($lnk, "/");
							$lnk = substr($lnk, $pos + 1);
							$pos = strpos($lnk, "?");
							if ($pos == false)
								$pos = strlen($lnk);
							$lnk = substr($lnk, 0, $pos);
							if ($app_lnk == $lnk)  
							{
								$acc = access_string($app->name);
								return array($acc[0], $module->name, $application->label, $app->id);
							}	
						}	
					}
				}
			}
			return array("", "", "", $sel_app);
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
			global $path_to_root, $SysPrefs, $version;

			$sel_app = $_SESSION['sel_app'];
			echo "<div class='fa-main'>\n";
			if (!$no_menu)
			{
				echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery-latest.min.js'></script>\n";
				echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/script.js'></script>\n";
				$applications = $_SESSION['App']->applications;
				$local_path_to_root = $path_to_root;
				$img = "<img src='$local_path_to_root/themes/".user_theme()."/images/login.gif' width='14' height='14' border='0' alt='"._('Logout')."'>&nbsp;&nbsp;";
				$himg = "<img src='$local_path_to_root/themes/".user_theme()."/images/help.gif' width='14' height='14' border='0' alt='"._('Help')."'>&nbsp;&nbsp;";
				echo "<div id='header'>\n";
				echo "<ul>\n";
				$logo = "$path_to_root/themes/".user_theme()."/images/logo_frontaccounting.png";
				if (file_exists($logo))
					echo "<li><img src='$logo' height='30' border='0' onload='fixPNG(this)' alt=''></li>\n";				
				else
				{
					echo "  <li><a href='$path_to_root/admin/display_prefs.php?'>" . _("Preferences") . "</a></li>\n";
					echo "  <li><a href='$path_to_root/admin/change_current_user_password.php?selected_id=" . $_SESSION["wa_current_user"]->username . "'>" . _("Change password") . "</a></li>\n";
			 		if ($SysPrefs->help_base_url != null)
						echo "  <li><a target = '_blank' onclick=" .'"'."javascript:openWindow(this.href,this.target); return false;".'" '. "href='". 
							help_url()."'>$himg" . _("Help") . "</a></li>";
					echo "  <li><a href='$path_to_root/access/logout.php?'>$img" . _("Logout") . "</a></li>";
				}	
				echo "</ul>\n";
				$indicator = "$path_to_root/themes/".user_theme(). "/images/ajax-loader.gif";
				echo "<h1>$SysPrefs->power_by $version<span style='padding-left:300px;'><img id='ajaxmark' src='$indicator' align='center' style='visibility:hidden;'></span></h1>\n";
				echo "</div>\n"; // header
				echo "<ul id='saturday'>\n"; // horizontal menu
				foreach($applications as $app)
				{
                    if ($_SESSION["wa_current_user"]->check_application_access($app))
                    {
						if ($sel_app == $app->id)
							$sel_application = $app;
						$acc = access_string($app->name);
						echo "<li><a ".($sel_app == $app->id ? "class='selected' " : "") . "href='$local_path_to_root/index.php?application=" . $app->id
							."'$acc[1]'>" . $acc[0] . "</a></li>\n";
					}		
				}
				echo "</ul>\n"; // horizontal menu
			}				
			echo "<div class='fa-body'>\n";
			if (!$no_menu)
			{		
				add_access_extensions();
				echo "<div class='fa-side'>\n";
				echo "<div id='cssmenu'>\n";
				echo "<ul>\n";
				$app = $sel_application;
				
				$acc = access_string($app->name);
				echo "  <li class='ttitle'><a href='#'><span>$acc[0]</span></a></li>\n";
				$i = $j = 0;
				foreach ($app->modules as $module)
				{
        			if (!$_SESSION["wa_current_user"]->check_module_access($module))
        				continue;

					$account = $this->wa_get_apps($title, $applications, $sel_app);
					//if (($account[3] == $sel_app && $account[2] == $module->name) || $j == 0)
					if (($account[3] == $sel_app && $account[1] == $module->name))
						$class = "active";
					else						
						$class = "";
					/*					
        			if ($j == 0)
        				$class = "active";
        			else
        				$class = "";
					*/        				
   					echo "  <li class='has-sub $class'><a href='#'><span>$module->name</span></a>\n";
   					echo "    <ul>\n";	
					$apps = array();
					foreach ($module->lappfunctions as $appfunction)
						$apps[] = $appfunction;
					foreach ($module->rappfunctions as $appfunction)
						$apps[] = $appfunction;
					$application = array();	
					foreach ($apps as $application)	
					{
						$lnk = access_string($application->label);
						if ($_SESSION["wa_current_user"]->can_access_page($application->access))
						{
							if ($application->label != "")
							{	
							
								$link = "$path_to_root/$application->link";
								echo "      <li><a href='$link'><span>$lnk[0]</span></a></li>\n";
							}
						}
						elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items())	
							echo "      <li><a href='' ><span>$lnk[1]</span></a></li>\n";
					}
					$j++;
					echo "    </ul>\n";
					echo "  </li>\n";
				}
				echo "  <li class='ttitle'><a href='#'><span>" . $_SESSION["wa_current_user"]->name . "</span></a></li>\n";
				echo "  <li ><a href='$path_to_root/admin/display_prefs.php?'><span>" . _("Preferences") . "</span></a></li>\n";
				echo "  <li><a href='$path_to_root/admin/change_current_user_password.php?selected_id=" . $_SESSION["wa_current_user"]->username . "'><span>" . _("Change password") . "</span></a></li>\n";
			 	if ($SysPrefs->help_base_url != null)
					echo "  <li><a target = '_blank' onclick=" .'"'."javascript:openWindow(this.href,this.target); return false;".'" '. "href='". 
						help_url()."'><span>$himg  " . _("Help") . "</span></a></li>\n";
				echo "  <li class='last'><a href='$path_to_root/access/logout.php?'><span>$img  " . _("Logout") . "</span></a></li>";
				echo "</ul>\n";
				echo "</div>\n"; // cssmenu
				/*echo "<div class='clear'></div>\n";*/
				echo "</div>\n"; // fa-side
				echo "<div class='fa-content'>\n";
			}
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
			global $path_to_root, $SysPrefs, $version, $db_connections;
			include_once($path_to_root . "/includes/date_functions.inc");

			if (!$no_menu)
				echo "</div>\n"; // fa-content
			echo "</div>\n"; // fa-body
			if (!$no_menu)
			{
				echo "<div class='fa-footer'>\n";
				if (isset($_SESSION['wa_current_user']))
				{
					echo "<span class='power'><a target='_blank' href='$SysPrefs->power_url'>$SysPrefs->power_by $version</a></span>\n";
					echo "<span class='date'>".Today() . "&nbsp;" . Now()."</span>\n";
					echo "<span class='date'>" . $db_connections[$_SESSION["wa_current_user"]->company]["name"] . "</span>\n";
					echo "<span class='date'>" . $_SERVER['SERVER_NAME'] . "</span>\n";
					echo "<span class='date'>" . $_SESSION["wa_current_user"]->name . "</span>\n";
					echo "<span class='date'>" . _("Theme:") . " " . user_theme() . "</span>\n";
					echo "<span class='date'>".show_users_online()."</span>\n";
				}
				echo "</div>\n"; // footer
			}
			echo "</div>\n"; // fa-main
		}

		function display_applications(&$waapp)
		{
			global $path_to_root, $use_popup_windows;
			include_once("$path_to_root/includes/ui.inc");
			include_once($path_to_root . "/reporting/includes/class.graphic.inc");
			include($path_to_root . "/includes/system_tests.inc");

			if ($use_popup_windows)
			{
				echo "<script language='javascript'>\n";
				echo get_js_open_window(900, 500);
				echo "</script>\n"; 
			}
			$selected_app = $waapp->get_selected_application();
			if (!$_SESSION["wa_current_user"]->check_application_access($selected_app))
				return;
			// first have a look through the directory, 
			// and remove old temporary pdfs and pngs
			$dir = company_path(). '/pdf_files';
	
			if ($d = @opendir($dir)) {
				while (($file = readdir($d)) !== false) {
					if (!is_file($dir.'/'.$file) || $file == 'index.php') continue;
				// then check to see if this one is too old
					$ftime = filemtime($dir.'/'.$file);
				 // seems 3 min is enough for any report download, isn't it?
					if (time()-$ftime > 180){
						unlink($dir.'/'.$file);
					}
				}
				closedir($d);
			}
			
			//check_for_overdue_recurrent_invoices();
			if ($selected_app->id == "orders")
				display_customer_topten();
			elseif ($selected_app->id == "AP")
				display_supplier_topten();
			elseif ($selected_app->id == "stock")
				display_stock_topten();
			elseif ($selected_app->id == "manuf")
				display_stock_topten(true);
			elseif ($selected_app->id == "proj")
				display_dimension_topten();
			elseif ($selected_app->id == "GL")
				display_gl_info();
			else	
				display_all();
		}

        function check_application_access($waapp)
        {
            if (!$this->hide_inaccessible_menu_items())
            {
                return true;
            }
            
            foreach ($waapp->modules as $module)
            {
                if ($this->check_module_access($module))
                {
                    return true;
                }
            }
            
            return false;
                    
        }
        
        function check_module_access($module)
        {
            
            if (!$this->hide_inaccessible_menu_items())
            {
                return true;
            }
            
            if (sizeof($module->lappfunctions) > 0)
            {
                foreach ($module->lappfunctions as $appfunction)
                {
                    if ($appfunction->label != "" && $_SESSION["wa_current_user"]->can_access_page($appfunction->access))
                    {
                        return true;
                    }
                }
            }
            
            if (sizeof($module->rappfunctions) > 0)
            {
                foreach ($module->rappfunctions as $appfunction)
                {
                    if ($appfunction->label != "" && $_SESSION["wa_current_user"]->can_access_page($appfunction->access))
                    {
                        return true;
                    }
                }
            }
            
            return false;
            
        }
        
        function hide_inaccessible_menu_items()
        {
            global $hide_inaccessible_menu_items;
            
            if (!isset($hide_inaccessible_menu_items) || $hide_inaccessible_menu_items == 0)
            {
                return false;
            }
            
            else
            {
                return true;
            }
        }
	}
	
	function display_customer_topten()
	{
		global $path_to_root;;
		
		$pg = new graph();

		$today = Today();
		$title = customer_top($today, 10, 33, $pg);
		source_graphic($today, $title, _("Customer"), $pg);
		customer_trans($today);
		customer_recurrent_invoices($today);
	}
	
	function display_supplier_topten()
	{
		global $path_to_root;
		
		$pg = new graph();

		$today = Today();
		$title = supplier_top($today, 10, 33, $pg);
		source_graphic($today, $title, _("Supplier"), $pg);
		supplier_trans($today);
	}

	function display_stock_topten($manuf=false)
	{
		global $path_to_root;
		
		$pg = new graph();
		
		$today = Today();
		$title = stock_top($today, 10, 33, $manuf, $pg);
		$source = ($manuf) ? _("Manufacturing") : _("Items");
		source_graphic($today, $title, $source, $pg);
	}
	
	function display_dimension_topten()
	{
		global $path_to_root;
		
		$pg = new graph();

		$today = Today();
		$title = dimension_top($today, 10, 33, $pg);
		source_graphic($today, $title, _("Dimension"), $pg, 5);
	}	

	function display_gl_info()
	{
		global $path_to_root;
		
		$pg = new graph();

		$today = Today();
		$title = gl_top($today, 33, $pg);
		source_graphic($today, $title, _("Class"), $pg, 5);
	}	
	
	function display_all()
	{
		$today = Today();

		$pg = new graph();

		echo "<table width='95%'>";
		echo "<tr valign=top><td style='width:50%'>\n"; // outer table

		$title = customer_top($today, 3, 66, $pg);
		source_graphic($today, $title, _("Customer"), $pg);
		$title = supplier_top($today, 3, 66, $pg);
		source_graphic($today, $title, _("Supplier"), $pg);
		$title = stock_top($today, 3, 66, false, $pg);
		source_graphic($today, $title, _("Stock"), $pg);

		echo "</td><td style='width:50%'>\n";
		
		dimension_top($today, 3, 66);
		$title = gl_top($today, 66, $pg);
		source_graphic($today, $title, _("Class"), $pg, 5);
		stock_top($today, 3, 66, true);
		
		echo "</td></tr></table>\n";
	}

	function display_title($title, $colspan=2)
	{
		br();
		echo "<tr><td colspan=$colspan class='headingtext' style='text-align:center;border:0;height:40px;'>$title</td></tr>\n";
	}	

	function customer_top($today, $limit=10, $width="33", &$pg=null)
	{
		$begin = begin_fiscalyear();
		$begin1 = date2sql($begin);
		$today1 = date2sql($today);
		$sql = "SELECT SUM((ov_amount + ov_discount) * rate * IF(trans.type = ".ST_CUSTCREDIT.", -1, 1)) AS total,d.debtor_no, d.name FROM
			".TB_PREF."debtor_trans AS trans, ".TB_PREF."debtors_master AS d WHERE trans.debtor_no=d.debtor_no
			AND (trans.type = ".ST_SALESINVOICE." OR trans.type = ".ST_CUSTCREDIT.")
			AND tran_date >= '$begin1' AND tran_date <= '$today1' GROUP by d.debtor_no ORDER BY total DESC, d.debtor_no 
			LIMIT $limit";
		$result = db_query($sql);
		$title = _("Top $limit customers in fiscal year");
		$th = array(_("Customer"), _("Amount"));
		start_table(TABLESTYLE, "width='$width%'");
		display_title($title, count($th));
		table_header($th);
		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = db_fetch($result))
		{
	    	alt_table_row_color($k);
	    	$name = $myrow["debtor_no"]." ".$myrow["name"];
    		label_cell($name);
		    amount_cell($myrow['total']);
		    if ($pg != null)
		    {
		    	$pg->x[$i] = $name; 
		    	$pg->y[$i] = $myrow['total'];
		    }	
		    $i++;
			end_row();
		}
		end_table(2);
		return $title;
	}

	function supplier_top($today, $limit=10, $width="33", &$pg=null)
	{
		$begin = begin_fiscalyear();
		$begin1 = date2sql($begin);
		$today1 = date2sql($today);
		$sql = "SELECT SUM((trans.ov_amount + trans.ov_discount) * rate) AS total, s.supplier_id, s.supp_name FROM
			".TB_PREF."supp_trans AS trans, ".TB_PREF."suppliers AS s WHERE trans.supplier_id=s.supplier_id
			AND (trans.type = ".ST_SUPPINVOICE." OR trans.type = ".ST_SUPPCREDIT.")
			AND tran_date >= '$begin1' AND tran_date <= '$today1' GROUP by s.supplier_id ORDER BY total DESC, s.supplier_id 
			LIMIT $limit";
		$result = db_query($sql);
		$title = _("Top $limit suppliers in fiscal year");
		$th = array(_("Supplier"), _("Amount"));
		start_table(TABLESTYLE, "width='$width%'");
		display_title($title, count($th));
		table_header($th);
		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = db_fetch($result))
		{
	    	alt_table_row_color($k);
	    	$name = $myrow["supplier_id"]." ".$myrow["supp_name"];
    		label_cell($name);
		    amount_cell($myrow['total']);
		    if ($pg != null)
		    {
		    	$pg->x[$i] = $name; 
		    	$pg->y[$i] = $myrow['total'];
		    }	
		    $i++;
			end_row();
		}
		end_table(2);
		return $title;
	}

	function stock_top($today, $limit=10, $width="33", $manuf=false, &$pg=null)
	{
		$begin = begin_fiscalyear();
		$begin1 = date2sql($begin);
		$today1 = date2sql($today);
		$sql = "SELECT SUM((trans.unit_price * trans.quantity) * d.rate) AS total, s.stock_id, s.description, 
			SUM(trans.quantity) AS qty FROM
			".TB_PREF."debtor_trans_details AS trans, ".TB_PREF."stock_master AS s, ".TB_PREF."debtor_trans AS d 
			WHERE trans.stock_id=s.stock_id AND trans.debtor_trans_type=d.type AND trans.debtor_trans_no=d.trans_no
			AND (d.type = ".ST_SALESINVOICE." OR d.type = ".ST_CUSTCREDIT.") ";
		if ($manuf)
			$sql .= "AND s.mb_flag='M' ";
		$sql .= "AND d.tran_date >= '$begin1' AND d.tran_date <= '$today1' GROUP by s.stock_id ORDER BY total DESC, s.stock_id 
			LIMIT $limit";
		$result = db_query($sql);
		if ($manuf)
			$title = _("Top $limit Manufactured Items in fiscal year");
		else	
			$title = _("Top $limit Sold Items in fiscal year");
		$th = array(_("Item"), _("Amount"), _("Quantity"));
		start_table(TABLESTYLE, "width='$width%'");
		display_title($title, count($th));	
		table_header($th);
		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = db_fetch($result))
		{
	    	alt_table_row_color($k);
	    	$name = $myrow["description"];
    		label_cell($name);
		    amount_cell($myrow['total']);
		    qty_cell($myrow['qty']);
		    if ($pg != NULL)
		    {
		    	$pg->x[$i] = $name; 
		    	$pg->y[$i] = $myrow['total'];
		    }	
		    $i++;
			end_row();
		}
		end_table(2);
	}
	
	function dimension_top($today, $limit=10, $width="33", &$pg=null)
	{

		$begin = begin_fiscalyear();
		$begin1 = date2sql($begin);
		$today1 = date2sql($today);
		$sql = "SELECT SUM(-t.amount) AS total, d.reference, d.name FROM
			".TB_PREF."gl_trans AS t,".TB_PREF."dimensions AS d WHERE
			(t.dimension_id = d.id OR t.dimension2_id = d.id) AND
			t.tran_date >= '$begin1' AND t.tran_date <= '$today1' GROUP BY d.id ORDER BY total DESC LIMIT $limit";
		$result = db_query($sql, "Transactions could not be calculated");
		$title = _("Top $limit Dimensions in fiscal year");
		$th = array(_("Dimension"), _("Amount"));
		start_table(TABLESTYLE, "width='$width%'");
		display_title($title, count($th));
		table_header($th);
		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = db_fetch($result))
		{
	    	alt_table_row_color($k);
	    	$name = $myrow['reference']." ".$myrow["name"];
    		label_cell($name);
		    amount_cell($myrow['total']);
		    if ($pg != null)
		    {
		    	$pg->x[$i] = $name; 
		    	$pg->y[$i] = abs($myrow['total']);
		    }	
		    $i++;
			end_row();
		}
		end_table(2);
	}
	
	function gl_top($today, $width="33", &$pg=null)
	{
		$begin = begin_fiscalyear();
		$begin1 = date2sql($begin);
		$today1 = date2sql($today);
		$sql = "SELECT SUM(amount) AS total, c.class_name, c.ctype FROM
			".TB_PREF."gl_trans,".TB_PREF."chart_master AS a, ".TB_PREF."chart_types AS t, 
			".TB_PREF."chart_class AS c WHERE
			account = a.account_code AND a.account_type = t.id AND t.class_id = c.cid
			AND IF(c.ctype > 3, tran_date >= '$begin1', tran_date >= '0000-00-00') 
			AND tran_date <= '$today1' GROUP BY c.cid ORDER BY c.cid"; 
		$result = db_query($sql, "Transactions could not be calculated");
		$title = _("Class Balances");
		start_table(TABLESTYLE2, "width='$width%'");
		display_title($title);
		$i = 0;
		$total = 0;
		while ($myrow = db_fetch($result))
		{
			if ($myrow['ctype'] > 3)
			{
		    	$total += $myrow['total'];
				$myrow['total'] = -$myrow['total'];
				if ($pg != null)
				{
		    		$pg->x[$i] = $myrow['class_name']; 
		    		$pg->y[$i] = abs($myrow['total']);
		    	}	
		    	$i++;
		    }	
			label_row($myrow['class_name'], number_format2($myrow['total'], user_price_dec()), 
				"class='label' style='font-weight:bold;'", "style='font-weight:bold;' align=right");
		}
		$calculated = _("Calculated Return");
		label_row("&nbsp;", "");
		label_row($calculated, number_format2(-$total, user_price_dec()), 
			"class='label' style='font-weight:bold;'", "style='font-weight:bold;' align=right");
		if ($pg != null)
		{
    		$pg->x[$i] = $calculated; 
    		$pg->y[$i] = -$total;
		}
		end_table(2);
	}
	
	function source_graphic($today, $title, $source, $pg, $type=2)
	{
		$pg->title     = $title;
		$pg->axis_x    = $source;
		$pg->axis_y    = _("Amount");
		$pg->graphic_1 = $today;
		$pg->type      = $type;
		$pg->skin      = 1;
		$pg->built_in  = false;
		$filename = company_path(). "/pdf_files/". uniqid("").".png";
		$pg->display($filename, true);
		start_table(TABLESTYLE);
		start_row();
		echo "<td>";
		echo "<img src='$filename' border='0' alt='$title'>";
		echo "</td>";
		end_row();
		end_table(1);
	}
	
	function customer_trans($today)
	{
		$today = date2sql($today);
		
		$sql = "SELECT trans.trans_no, trans.reference,	trans.tran_date, trans.due_date, debtor.debtor_no, 
			debtor.name, branch.br_name, debtor.curr_code,
			(trans.ov_amount + trans.ov_gst + trans.ov_freight 
				+ trans.ov_freight_tax + trans.ov_discount)	AS total,  
			(trans.ov_amount + trans.ov_gst + trans.ov_freight 
				+ trans.ov_freight_tax + trans.ov_discount - trans.alloc) AS remainder,
			DATEDIFF('$today', trans.due_date) AS days 	
			FROM ".TB_PREF."debtor_trans as trans, ".TB_PREF."debtors_master as debtor, 
				".TB_PREF."cust_branch as branch
			WHERE debtor.debtor_no = trans.debtor_no AND trans.branch_code = branch.branch_code
				AND trans.type = ".ST_SALESINVOICE." AND (trans.ov_amount + trans.ov_gst + trans.ov_freight 
				+ trans.ov_freight_tax + trans.ov_discount - trans.alloc) > ".FLOAT_COMP_DELTA." 
				AND DATEDIFF('$today', trans.due_date) > 0 ORDER BY days DESC";
		$result = db_query($sql);
		$title = db_num_rows($result) . _(" overdue Sales Invoices");
		br(1);
		display_heading($title);
		br();
		$th = array("#", _("Ref."), _("Date"), _("Due Date"), _("Customer"), _("Branch"), _("Currency"), 
			_("Total"), _("Remainder"),	_("Days"));
		start_table(TABLESTYLE);
		table_header($th);
		$k = 0; //row colour counter
		while ($myrow = db_fetch($result))
		{
	    	alt_table_row_color($k);
			label_cell(get_trans_view_str(ST_SALESINVOICE, $myrow["trans_no"]));
			label_cell($myrow['reference']);
			label_cell(sql2date($myrow['tran_date']));
			label_cell(sql2date($myrow['due_date']));
	    	$name = $myrow["debtor_no"]." ".$myrow["name"];
    		label_cell($name);
    		label_cell($myrow['br_name']);
    		label_cell($myrow['curr_code']);
		    amount_cell($myrow['total']);
		    amount_cell($myrow['remainder']);
		    label_cell($myrow['days'], "align='right'");
			end_row();
		}
		end_table(2);
	}

	function calculate_next_invoice($myrow)
	{
		if ($myrow["last_sent"] == '0000-00-00')
			$next = sql2date($myrow["begin"]);
		else
			$next = sql2date($myrow["last_sent"]);
		$next = add_months($next, $myrow['monthly']);
		$next = add_days($next, $myrow['days']);
		return add_days($next,-1);
	}

	function customer_recurrent_invoices($today)
	{
		$result = get_recurrent_invoices($today);
		$title = _("Overdue Recurrent Invoices");
		br(1);
		display_heading($title);
		br();
		$th = array(_("Description"), _("Template No"),_("Customer"),_("Branch")."/"._("Group"),_("Next invoice"));
		start_table(TABLESTYLE, "width=70%");
		table_header($th);
		$k = 0;
		while ($myrow = db_fetch($result)) 
		{
			if (!$myrow['overdue'])
				continue;
			alt_table_row_color($k);

			label_cell($myrow["description"]);
			label_cell(get_customer_trans_view_str(ST_SALESORDER, $myrow["order_no"]));
			if ($myrow["debtor_no"] == 0)
			{
				label_cell("");

				label_cell(get_sales_group_name($myrow["group_no"]));
			}
			else
			{
				label_cell(get_customer_name($myrow["debtor_no"]));
				label_cell(get_branch_name($myrow['group_no']));
			}
			label_cell(calculate_next_invoice($myrow),  "align='center'");
			end_row();
		}
		end_table(2);
	}

	function supplier_trans($today)
	{
		$today = date2sql($today);
		$sql = "SELECT trans.trans_no, trans.reference, trans.tran_date, trans.due_date, s.supplier_id, 
			s.supp_name, s.curr_code,
			(trans.ov_amount + trans.ov_gst + trans.ov_discount) AS total,  
			(trans.ov_amount + trans.ov_gst + trans.ov_discount - trans.alloc) AS remainder,
			DATEDIFF('$today', trans.due_date) AS days 	
			FROM ".TB_PREF."supp_trans as trans, ".TB_PREF."suppliers as s 
			WHERE s.supplier_id = trans.supplier_id
				AND trans.type = ".ST_SUPPINVOICE." AND (ABS(trans.ov_amount + trans.ov_gst + 
					trans.ov_discount) - trans.alloc) > ".FLOAT_COMP_DELTA."
				AND DATEDIFF('$today', trans.due_date) > 0 ORDER BY days DESC";
		$result = db_query($sql);
		$title = db_num_rows($result) . _(" overdue Purchase Invoices");
		br(1);
		display_heading($title);
		br();
		$th = array("#", _("Ref."), _("Date"), _("Due Date"), _("Supplier"), _("Currency"), _("Total"), 
			_("Remainder"),	_("Days"));
		start_table(TABLESTYLE);
		table_header($th);
		$k = 0; //row colour counter
		while ($myrow = db_fetch($result))
		{
	    	alt_table_row_color($k);
			label_cell(get_trans_view_str(ST_SUPPINVOICE, $myrow["trans_no"]));
			label_cell($myrow['reference']);
			label_cell(sql2date($myrow['tran_date']));
			label_cell(sql2date($myrow['due_date']));
	    	$name = $myrow["supplier_id"]." ".$myrow["supp_name"];
    		label_cell($name);
    		label_cell($myrow['curr_code']);
		    amount_cell($myrow['total']);
		    amount_cell($myrow['remainder']);
		    label_cell($myrow['days'], "align='right'");
			end_row();
		}
		end_table(2);
	}

