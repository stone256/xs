
<style>
.wrapper--nav_back {
  background: url(/media/image/bb2.jpg) no-repeat 50% 100%;
  background-size: cover, contain;
  position:fixed;
  width:100%;
  z-index:6009;
  top:0;
  min-height: 60px;
}
.wrapper--nav {
  background-color: #2c2d3a;
  position:fixed;
  width:100%;
  z-index:6009;
  top:0;
  min-height: 60px;
}
.logo {
    float: left;
    color: #fff;
    font-size: 24px;
    font-weight: 600;
    letter-spacing: 3px;
    text-transform: uppercase;
    padding-top: 10px;
}
.logged-in #cssmenu a {
  color: #FFF;
}
.logged-out #cssmenu a {
  color: #FFF;
}

#cssmenu {
    padding-left: 200px;

    font-weight:700;
}

#cssmenu a {
    text-decoration: none;
    white-space: nowrap;
    text-transform:  uppercase;
    letter-spacing: 2px;
    font-size: 13px;
    font-weight: 200;
}
#cssmenu > li > ul.sub-menu {
    background: #777;
    margin-top: 10px;
    padding: 5px;
}
#cssmenu >ul {
padding:0;
}
#cssmenu ul {
  list-style: none;
  margin: 12px;
}
#cssmenu > ul > li {
  display: inline-block;
  padding: 8px 50px 8px 15px;

}
.has-sub {
    position: relative;
}
#cssmenu  .has-sub > .sub-menu {
    display: block;
    padding: 0;
    position: absolute;
    z-index: 1000;
    background: #f8efdd;
    border: 1px solid #dfdfdf;
    border-top: none;
    top:100%;
    left: 0;
}
#cssmenu .sub-menu.open {
    display: block;
}
#cssmenu .sub-menu.close {
    display: none;
}
#cssmenu .sub-menu .sub-menu {
        position: absolute;
        background: #f8efdd;
        border: 1px solid #dfdfdf;
        top:1%;
        left: 94%;

    }
#cssmenu .sub-menu .sub-menu a, #cssmenu  .has-sub > .sub-menu a {
        color: #333;
}
#cssmenu .sub-menu .sub-menu a:hover, #cssmenu  .has-sub > .sub-menu a:hover {
        color: #007bff;
}
#cssmenu .mlevel_3.sub-menu{
background:#4976d2;
}
#cssmenu  li > ul > li {
  display: block;
  float: none;
  padding: 8px 50px 4px 15px;
  width: 100%;
}
#cssmenu  li > ul > li li {
  margin: 5px;
}
.badge-secondary {
    color: #fff;
    background-color: #6c757d;
}
.badge-pill {
    padding-right: .9em;
    padding-left: .9em;
    border-radius: 5rem;
}
.badge {
    display: inline-block;
    padding: .45em .9em;
    line-height: 1;
    border-radius: .99rem;
    margin-left: 8px;
    font-size: 10px;
}


.mobile {display: none;}
.mobile .fa { color : #eee; margin: 12px;}
#cssmenu .has-sub.up > a:after {  font-family: FontAwesome;    padding-left:10px; content:  "\F0D7"; width:1.3em ;text-algin:center; display:inline-block}
#cssmenu .has-sub.down > a:after { font-family: FontAwesome;  padding-left:10px; content:  "\F0DA"; width:1.3em;text-algin:center; display:inline-block}
/** system message alert **/
#cssmenu .has-sub.system_message > a:after { padding-left:10px; content:  " "; width:1.3em ;text-algin:center; display:inline-block}

#cssmenu li > a:only-child:after { content: ''; }

@media all and (max-width : 768px) {

   .mobile {display: block;font-size:1.8em;padding:0.15em}
   #cssmenu {display: none;}

   #cssmenu .sub-menu.open {
    display: block;
}
   #cssmenu .sub-menu.close {
    display: none;
}

   #cssmenu li {

      float: none;
      margin-left: 20px;
      width: 100%;
      margin: 5px 0;
    }
    #cssmenu  .has-sub > .sub-menu {

    padding: 0;
    position: relative;
    z-index: 0;
    background: #f8efdd;
    color: #000;
    top: 5px;
}
    #cssmenu .sub-menu .sub-menu {
        position: relative;
        background: #fff;
        left:20px

    }
    #cssmenu > ul > li {
        padding-bottom: 0px;
    }
    #cssmenu > ul  {
        padding-left: 0px;
    }
}
</style>
