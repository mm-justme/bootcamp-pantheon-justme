(function (Drupal) {
  'use strict';

  Drupal.behaviors.changeElementOpacity = {
    attach: function (context) {
      once('changeElementOpacity', '.changeOpacity', context).forEach(function (element) {
        const target = document.querySelector('.changeOpacity');

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                target.style.opacity = 1;
                obs.unobserve(entry.target);
              }
            });
          },
          {
            rootMargin: '0px 0px -60% 0px',
          },
        );
        observer.observe(target);

      });
    },
  };
})(Drupal);
