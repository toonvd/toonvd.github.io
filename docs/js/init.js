"use strict";

(function(){
  handleSideNav();
  initHightlightJS();
})()

function handleSideNav() {
  let sidenavs = document.querySelectorAll('.sidenav')
  for (var i = 0; i < sidenavs.length; i++){
    M.Sidenav.init(sidenavs[i]);
  }
}

function initHightlightJS() {
  document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('pre code').forEach((element) => {
      hljs.highlightElement(element);
    });
  });
}