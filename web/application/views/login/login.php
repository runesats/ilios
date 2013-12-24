<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Login page template.
 *
 * @copyright Copyright (c) 2010-2012 The Regents of the University of California.
 * @license http://www.iliosproject.org/license GNU GPL v3
 */
$controllerURL = site_url() . '/authentication_controller'; // TODO: consider how to avoid this coupling
$dashboardControllerUrl = site_url() . '/dashboard_controller';
$viewsUrlRoot = getViewsURLRoot();
$viewsPath = getServerFilePath('views');

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title><?php echo t('login.title'); ?></title>
    <meta name="description" content="">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->
    <link rel="stylesheet" href="<?php echo appendRevision($viewsUrlRoot . "css/ilios-styles.css"); ?>" media="all">
    <link rel="stylesheet" href="<?php echo appendRevision($viewsUrlRoot . "css/custom.css"); ?>" media="all">

    <!-- Third party JS -->
    <script type="text/javascript" src="<?php echo appendRevision($viewsUrlRoot . "scripts/third_party/html5shiv.js"); ?>"></script>
    <script type="text/javascript" src="<?php echo appendRevision($viewsUrlRoot . "scripts/third_party/yui_kitchensink.js"); ?>"></script>

    <!-- Ilios JS -->
    <script type="text/javascript" src="<?php echo $controllerURL; ?>/getI18NJavascriptVendor"></script>
    <script type="text/javascript" src="<?php echo appendRevision($viewsUrlRoot . "scripts/ilios_base.js"); ?>"></script>
    <script type="text/javascript" src="<?php echo appendRevision($viewsUrlRoot . "scripts/ilios_ui.js"); ?>"></script>
    <script type="text/javascript" src="<?php echo appendRevision($viewsUrlRoot . "scripts/ilios_dom.js"); ?>"></script>
    <script type="text/javascript" src="<?php echo appendRevision($viewsUrlRoot . "scripts/ilios_utilities.js"); ?>"></script>
    <script type="text/javascript">
        var controllerURL = "<?php echo $controllerURL; ?>/";                 // expose this to our *.js
        var dashboardControllerUrl = "<?php echo $dashboardControllerUrl; ?>";
    </script>
    <script type="text/javascript" src="<?php echo appendRevision($viewsUrlRoot . "login/login_transaction.js"); ?>"></script>
</head>
<body class="yui-skin-sam">
    <div id="wrapper">
        <header id="masthead" class="clearfix">
            <div class="inner">
<?php
    include_once $viewsPath . 'common/masthead_logo.inc.php';
    include_once $viewsPath . 'common/masthead_toolbar.inc.php';
?>
           </div>
<?php include_once $viewsPath . 'common/masthead_viewbar.inc.php'; ?>
        </header>
        <div id="main" role="main">
            <div id="content" class="clearfix">
                <form method="post" action="<?php echo $controllerURL. '/login'; ?>">
                    <div style="font-size: 16pt; margin-top: 12px; margin-bottom: 12px; position: relative;">
                        <center id="login_status_message"><?php echo $login_message; ?></center>
                    </div>
                    <div id="login_panel_div"
                         style="margin: auto;  padding: 0.5em; width: 17em;
                                 background-color: #696B61; color: #FCF8E2; border: 1px solid #3A325A;">
                        <label for="user_name"><?php echo t('general.terms.username'); ?></label>
                        <input type="text" id="user_name" name="user_name" value=""
                                style="margin-right: 2px; float: right; width: 160px;" />
                        <div style="height: 9px;" class="clear"></div>
                        <label for="password"><?php echo t('general.terms.password'); ?></label>
                        <input type="password" id="password" name="password" value=""
                                style="margin-right: 2px; float: right; width: 160px;" />
                        <div style="height: 6px;" class="clear"></div>
                        <button type="submit" id="login_button" style="margin-right: 9px; float: right;">
                            <?php echo t('general.terms.login'); ?>
                        </button>
                        <div class="clear"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
    <!-- reserve for later use -->
    </footer>
    <!-- overlays at the bottom - avoid z-index issues -->
    <div id="view-menu"></div>

    <script type="text/javascript">
        // register alert/inform overrides on window load
        YAHOO.util.Event.on(window, 'load', function() {
            window.alert = ilios.alert.alert;
            window.inform = ilios.alert.inform;
        });

        function handleUserNameFieldInput (inputField, event) {
            var charCode = event.keyCode ? event.keyCode : (event.which ? event.which : event.charCode);
            if (charCode == 13) {
                var passwordField = document.getElementById('password');
                passwordField.focus();
                event.cancelBubble = true;
                event.returnValue = false;
                return false;
            }
            return true;
        }

        function handlePasswordFieldInput (inputField, event) {
            var charCode = event.keyCode ? event.keyCode : (event.which ? event.which : event.charCode);
            if (charCode == 13) {
                var button = document.getElementById('login_button');
                button.click();
                event.cancelBubble = true;
                event.returnValue = false;
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
