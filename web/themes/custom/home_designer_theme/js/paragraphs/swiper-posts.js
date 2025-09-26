(function(Drupal) {
  Drupal.behaviors.latestPostsSwiper = {
    attach(context) {
      once("latestPostsSwiper", ".js-swiper", context).forEach(el => {
        const controls = document.createElement("div");
        const buttonPrev = document.createElement("div");
        const buttonNext = document.createElement("div");

        controls.classList.add("swiper-controls");
        buttonPrev.classList.add("swiper-button-prev");
        buttonNext.classList.add("swiper-button-next");

        el.appendChild(controls);
        controls.appendChild(buttonPrev);
        controls.appendChild(buttonNext);

        new Swiper(el, {
          slidesPerView: 3,
          slidesPerGroup: 3,
          spaceBetween: 16,
          allowTouchMove: false,
          autoHeight: true,
          loop: false,
          breakpoints: {
            0: {
              slidesPerView: 1,
              slidesPerGroup: 1
            },
            769: {
              slidesPerView: 2,
              slidesPerGroup: 2
            },
            1024: {
              slidesPerView: 3,
              slidesPerGroup: 3
            }
          },

          navigation: {
            nextEl: el.querySelector(".swiper-button-next"),
            prevEl: el.querySelector(".swiper-button-prev")
          }
        });
      });
    }
  };
})(Drupal);
