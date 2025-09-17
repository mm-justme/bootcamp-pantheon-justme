(function(Drupal) {
  Drupal.behaviors.changeImgOpacity = {
    attach(context) {
      once("changeImgOpacity", ".is-visible", context).forEach(el => {
        const observer = new IntersectionObserver(
          (entries, obs) => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                el.style.opacity = 1;

                obs.unobserve(entry.target);
              }
            });
          },
          {
            rootMargin: "0px 0px -60% 0px"
          }
        );
        observer.observe(el);
      });
    }
  };
})(Drupal);
