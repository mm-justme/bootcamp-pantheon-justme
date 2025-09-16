(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.hoverChangeBackground = {
    attach: function (context) {
      console.log("Test start");
      once('hoverChangeBackground', '.wrapper', context).forEach(function (element) {
        console.log("Test start ONCE");

      });
      console.log("Test end");
    }
  };

})(Drupal, once);
