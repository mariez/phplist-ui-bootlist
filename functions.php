<?php

// TODO: Remove after resolution of Mantis #18945
if (
    isset($page_title) 
    && $page_title =='userclicks'
) {
    $page_title = s('Click Statistics');
}


/* This array is to add third level to phpList menu, adding orphan items to a menulink */

$GLOBALS['subcat'] = array(
	'import' => array ('import1','import2','import3','import4','importsimple'),
	'users' => array('user','userhistory'),
	'list' => array('members','editlist'),
	'usermgt' => array('massremove','usercheck','reconcileusers'),
	'messages' => array('message'),
	'templates' => array('template'),
	'system' => array('converttoutf8'),
	'bouncemgt' => array('bounce','bounces','processbounces','generatebouncerules'),
	'bouncerules' => array('bouncerule'),
	'spage' => array('spageedit'),
	'attributes' => array('editattributes','defaults'),
	);


/* This function is to add third level to phpList menu */
function pageSubCategory($menulinks = array(), $current) {
    foreach ($GLOBALS['subcat'] as $subcategory => $subcat_details) {
        if ( !in_array($current, $menulinks) /* <-first check if is not a menulink */ 
        	&& in_array($current, $subcat_details) /* then find the menulink in array above */ ) {
            return $subcategory;
        }
    }
    return '';
}


/* replace topmenu() function */
function _topMenu()
{
    if ( !isset($_GET['page'] ) ) { $_GET['page'] = ''; }
    $current_page = htmlentities($_GET['page']);
    if (empty($_SESSION['logindetails'])) {
        return '';
    }

    if ($_SESSION['logindetails']['superuser']) { // we don't have a system yet to distinguish access to plugins
        if (count($GLOBALS['plugins'])) {
            foreach ($GLOBALS['plugins'] as $pluginName => $plugin) {
                $menulinks = $plugin->topMenuLinks;
                foreach ($menulinks as $link => $linkDetails) {
                    if (isset($GLOBALS['pagecategories'][$linkDetails['category']])) {
                        array_push($GLOBALS['pagecategories'][$linkDetails['category']]['menulinks'],
                            $link . '&pi=' . $pluginName);
                    }
                }
            }
        }
    }

    $topmenu = '';
    $topmenu .= '<div id="menuTop">';
    if (!DEVVERSION) {
        unset($GLOBALS['pagecategories']['develop']);
    }

    foreach ($GLOBALS['pagecategories'] as $category => $categoryDetails) {
        if ($category == 'hide') {
            continue;
        }

        $thismenu = '';
        $icon = 'glyphicon-plus';
        $icontext = "";
        $open = '';
        $accmenu = '';
        switch ($category) {
			case "dashboard" : $icon = "glyphicon-home"; break;
			case "subscribers" : $icon = "glyphicon-user"; break;
			case "campaigns" : $icon = "glyphicon-envelope"; break;
			case "statistics" : $icon = "glyphicon-stats"; break;
			case "system" : $icon = "glyphicon-wrench"; break;
			case "config" : $icon = "glyphicon-cog"; break;
			case "info" : $icon = ""; $icontext = "<samp style='line-height:1.5;font-weight:bold;font-size:19px'>i</samp>"; break;
			case "develop" : $icon = "glyphicon-console"; break;
        }
        foreach ($categoryDetails['menulinks'] as $page) {
                $title = $GLOBALS['I18N']->pageTitle($page);
                $active = '';
				if ( isset($_GET['pi']) && $page == $current_page.'&pi='.$_GET['pi']
                	|| !$_GET['pi'] && $page == $current_page 
                	|| $page == pageSubCategory($categoryDetails['menulinks'], $current_page) ) {
					   $active = ' class="active"';
                }
                elseif (!isset($_GET['pi']) && $category == pageCategory($current_page) ){ // third level
                    $open = ' class="active open"';
                }
                $link = PageLink2($page, $title, '', true);

				/* build account  menu ($accmenu) if Account section exist */
                if ($link && $category == 'account') {
					switch($page){
                			case "accinfo" : $icon = "glyphicon-briefcase"; $page_title ="Your account"; break;
							case "accsettings" : $icon = "glyphicon-wrench";  $page_title ="Account settings"; break;
							case "help" : $icon = "";$page_title = "Help"; $icontext="<samp style='line-height:1.5;font-weight:bold;font-size:19px'>?</samp>"; break;
					}
					if ($active == ' class="active"')  $active = ' class="open active"';
                    $accmenu .= '<ul><li '.$active.'.><a class="level0" href="' . PageUrl2($page, '', '', true). '" title="' . $title . '"><span class="glyphicon '.$icon.'">'.$icontext.'</span>' . ucfirst($page_title) . '</a></li></ul>';
                }
                
                /* add item to mainmenu ($thismenu) */
                 elseif ($link) {
                	$thismenu .= '<li' . $active . '>' . $link . '</li>';
                }
         }
        $twohomes = array('dashboard','home');
        if ( in_array($current_page,$twohomes) && $categoryDetails['toplink'] == 'dashboard' ) { // page 'home' redirect from dashboard
                    $open = ' class=" active open"';
        }
        if (!empty($thismenu)) {
            $thismenu = '<ul>' . $thismenu . '</ul>';
        }

        if ($category != 'account' && !empty($categoryDetails['toplink'])) {
            $categoryurl = PageUrl2($categoryDetails['toplink'], '', '', true);
            if ($categoryurl) {
            	$categoryurl = ($thismenu == "") ? $categoryurl : "#";
                $topmenu .= '<ul><li '.$open.' id="'.$category.'"><a class="level0" href="' . $categoryurl . '" title="' . $GLOBALS['I18N']->pageTitleHover($category) . '"><span class="glyphicon '.$icon.'">'.$icontext.'</span>' . ucfirst($GLOBALS['I18N']->get($category)) . '</a>' . $thismenu . '</li></ul>';
            } else {
                $topmenu .= '<ul><li><span>' . $GLOBALS['I18N']->get($category) . $categoryurl . '</span>' . $thismenu . '</li></ul>';
            }
        }/* <- end foreach menulinks */        
    } /* <- end foreach category */

	/* add an Account  section if category exist */
	if (!empty($accmenu)) {
		$topmenu.='<h3 id="accmenu">'.$GLOBALS['I18N']->get('Profile and account').'</h3>'.$accmenu;
	}
	
    $topmenu .= '</div>';
    return $topmenu;
}

