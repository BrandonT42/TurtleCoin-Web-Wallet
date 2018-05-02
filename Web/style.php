<?php
header("Content-type: text/css; charset: UTF-8");
require_once "core.php"
?>
body {
    font-family: 'Ruluko', sans-serif;
    font-size: 14px;
    background-color: <?php echo $_SESSION['websitecolor']['darkcolor']; ?>;
    color: <?php echo $_SESSION['websitecolor']['bodycolor']; ?>;
}


.navbar-header a {
    color: <?php echo $_SESSION['websitecolor']['textcolor']; ?> !important;
    padding-right:100px;
}

.text-center {
    text-align:center;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Ruluko', sans-serif;
}

h1 {
    line-height: 120px;
    text-transform: uppercase;
    color: <?php echo $_SESSION['websitecolor']['highlight']; ?>;
    font-size: 50px;
    font-weight: 900 !important;
    margin-top: -20px;
}
a.welcome-msg {
    color: <?php echo $_SESSION['websitecolor']['highlight']; ?> !important;
    text-transform: uppercase;
}
h3 {
    font-size: 20px;
    font-weight: 500 !important;
}
h4 {
    color: <?php echo $_SESSION['websitecolor']['bodycolor']; ?> !important;
    line-height: 40px;
    padding-bottom: 40px;
}
h5 {
    font-size: 16px;
    font-weight: 900;
}

p {
    font-weight:300;
    line-height:30px;
    padding-bottom:20px;
}

input {
    color: #000;
    font-size: 24px !important;
}
input:hover {
    cursor: text;
}

.nav-option:hover {
    color: <?php echo $_SESSION['websitecolor']['bodycolor']; ?> !important;
    -webkit-transition: all 400ms ease-in-out;
    -moz-transition: all 400ms ease-in-out;
    -o-transition: all 400ms ease-in-out;
    transition: all 400ms ease-in-out;
}
.nav-option {
    color: <?php echo $_SESSION['websitecolor']['navcolor']; ?> !important;
    -webkit-transition: all 400ms ease-in-out;
    -moz-transition: all 400ms ease-in-out;
    -o-transition: all 400ms ease-in-out;
    transition: all 400ms ease-in-out;
}

.text-input {
    border: 2px solid <?php echo $_SESSION['websitecolor']['bordercolor']; ?>;
    border-radius: 4px;
    width: 100%;
    margin: 0px;
    -webkit-transition: all 400ms ease-in-out;
    -moz-transition: all 400ms ease-in-out;
    -o-transition: all 400ms ease-in-out;
    transition: all 400ms ease-in-out;
}
.text-input:hover, text-input:active {
    border: 2px solid <?php echo $_SESSION['websitecolor']['highlight']; ?> !important;
    -webkit-transition: all 400ms ease-in-out;
    -moz-transition: all 400ms ease-in-out;
    -o-transition: all 400ms ease-in-out;
    transition: all 400ms ease-in-out;
}

.tx-link {
    color: <?php echo $_SESSION['websitecolor']['textcolor']; ?>;
}
.tx-link:hover, .tx-link:active {
    color: <?php echo $_SESSION['websitecolor']['highlight']; ?>;
}

.btn-primary, .btn-primary:visited {
    background-color: transparent;
    border-radius: 25px;
    border-color: <?php echo $_SESSION['websitecolor']['textcolor']; ?> !important;
    font-weight: 500;
    margin-left: 10px;
    margin-right: 10px;
    margin-bottom: 4px;
    -webkit-transition: all 400ms ease-in-out;
    -moz-transition: all 400ms ease-in-out;
    -o-transition: all 400ms ease-in-out;
    transition: all 400ms ease-in-out;
}

.btn-primary:hover, .btn-primary:active {
    background-color: <?php echo $_SESSION['websitecolor']['highlight']; ?> !important;
    border-color: <?php echo $_SESSION['websitecolor']['highlight']; ?> !important;
    -webkit-transition: all 400ms ease-in-out;
    -moz-transition: all 400ms ease-in-out;
    -o-transition: all 400ms ease-in-out;
    transition: all 400ms ease-in-out;
}

.disabled {
    background-color: <?php echo $_SESSION['websitecolor']['disabled']; ?> !important;
}

.space-pad {
    padding-bottom:50px;
}

.switch {
    font-family: 'Ruluko', sans-serif;
    font-size: 30px;
    font-weight: 500;
    color: <?php echo $_SESSION['websitecolor']['textcolor']; ?>;
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
    margin-left: 20%;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
    border-radius: 34px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: <?php echo $_SESSION['websitecolor']['highlight']; ?>;
}
input:focus + .slider {
    box-shadow: 0 0 1px <?php echo $_SESSION['websitecolor']['highlight']; ?>;
}
input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
}

.description-blob {
    margin-top: -20px;
    margin-bottom: -20px;
    font-size: 24px !important;
    word-wrap: break-word;
}
.address-blob {
    color: <?php echo $_SESSION['websitecolor']['highlight']; ?>;
    margin-top: -20px;
    margin-bottom: -20px;
    font-size: 20px !important;
    word-wrap: break-word;
}
.address-blob::selection {
    background: transparent;
}
.address-blob::-moz-selection {
    background: transparent;
}

.help-block {
    color: red;
    padding-bottom: -20px;
    margin-bottom: -20px;
}

.alert-message {
    font-weight: 600;
    margin-bottom: 30px;
    word-wrap: break-word;
}

.for-full-back {
     /* IE 8 */
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";

  /* IE 5-7 */
  filter: alpha(opacity=90);

  /* Netscape */
  -moz-opacity: 0.9;

  /* Safari 1.x */
  -khtml-opacity: 0.9;

  /* Good browsers */
  opacity: 0.9;
}

section {
    padding-top:30px;
    padding-bottom:40px;
}

.color-light {
    min-height: 100px;
    background-color: <?php echo $_SESSION['websitecolor']['lightcolor']; ?> !important;
    color: <?php echo $_SESSION['websitecolor']['lightcolortext']; ?>;
}
.color-light h3 {
    color: <?php echo $_SESSION['websitecolor']['lightcolortext']; ?>;
}
.color-light .btn-primary {
    color: <?php echo $_SESSION['websitecolor']['textcolor']; ?> !important;
}

.color-dark {
    min-height: 100px;
    background-color: <?php echo $_SESSION['websitecolor']['darkcolor']; ?> !important;
    color: <?php echo $_SESSION['websitecolor']['darkcolortext']; ?>;
}
.color-dark h3 {
    color: <?php echo $_SESSION['websitecolor']['darkcolortext']; ?>;
    color: <?php echo $_SESSION['websitecolor']['darkcolortext']; ?>;
}
.color-light .btn-primary {
    color: <?php echo $_SESSION['websitecolor']['textcolor']; ?> !important;
}

.color-green {
    color: <?php echo $_SESSION['websitecolor']['highlight']; ?> !important;
}

.nav {
	padding-right: 0px;
    padding-left: 10px;
}
.fixed {
	position: fixed; 
	top: 0; 
	min-height: 50px; 
	z-index: 99;
}

.navbar-inverse {
    background-color: <?php echo $_SESSION['websitecolor']['darkcolor']; ?>;
    border-color: <?php echo $_SESSION['websitecolor']['darkcolor']; ?>;
}

.navbar {
    background-color: <?php echo $_SESSION['websitecolor']['headercolor']; ?> !important;
}

.navbar-brand {
    color: <?php echo $_SESSION['websitecolor']['highlight']; ?> !important;
    max-width: 200px;
}

.navbar-header {
    white-space: nowrap;
}

.about-div {
    width: 100%;
    min-height: 100px;
    background-color: <?php echo $_SESSION['websitecolor']['darkcolor']; ?>;
    box-shadow: none;
    border: 0;
    padding: 20px 20px 20px 20px;
    margin-bottom: 20px;
    font-weight: 500;
}

#footer {
    background-color:<?php echo $_SESSION['websitecolor']['footercolor']; ?>;
    color: <?php echo $_SESSION['websitecolor']['navcolor']; ?>;
    padding:20px 50px 20px 50px;
    text-align:right;
}
