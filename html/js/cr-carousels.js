
$(document).ready(function ($) {
    var excuted = true;

    if (excuted) {
        setTimeout(function () {
            startCarousel('#cost-carousel-mobile');
            startCarousel('#how-works-mobile');
            startCarousel('#reviews-mobile');
        }, 100);
        excuted = false;
    }

    function startCarousel(carouselName) {

        var carouselItems = $(carouselName + ' .carousel').children('li');
        
        carouselItems.each(function () {
            var container = $(this);


            // update carousel on swipeleft
            container.find('.carousel-boundaries').on('swipeleft', function (event) {
                event.preventDefault();
                var wrapper = $(this);
                if (wrapper.find('.selected').is(':last-child')) {
                    prevSlide(container, 0);
                } else {
                    nextSlide(container);
                }
            });

            // update carousel on swiperight
            container.find('.carousel-boundaries').on('swiperight', function (event) {
                event.preventDefault();
                var wrapper = $(this);
                if (wrapper.find('.selected').is(':first-child')) {
                    nextSlide(container, wrapper.children('li').length - 1);
                } else {
                    prevSlide(container);
                }
            });

            // preview image hover effect - desktop only
            container.on('mouseover', '.move-right, .move-left', function (event) {
                event.preventDefault();
                hoverItem($(this), true);
            });

            container.on('mouseleave', '.move-right, .move-left', function (event) {
                event.preventDefault();
                hoverItem($(this), false);
            });

            // update carousel when user clicks on the preview images
            container.on('click', '.move-right, .move-left', function (event) {
                event.preventDefault();
                var selectedPosition;

                if ($(this).hasClass('move-right')) {
                    selectedPosition = container.find('.carousel-boundaries .selected').index() + 1;
                    nextSlide(container, selectedPosition);
                } else {
                    selectedPosition = container.find('.carousel-boundaries .selected').index() - 1;
                    prevSlide(container, selectedPosition);
                }
            });

            container.parent().siblings(carouselName + ' .next').on("click", function (event) {
                nextHandler(event)
            });
            container.parent().siblings(carouselName + ' .prev').on("click", function (event) {
                prevHandler(event)
            });

            function nextHandler(event) {
                event.preventDefault();

                var wrapper = container.find('.carousel-boundaries');
                if (wrapper.find('.selected').is(':last-child')) {
                    var selectedPosition = container.find('.carousel-boundaries .selected').index() - (wrapper.children('li').length - 1);
                    prevSlide(container, selectedPosition);
                } else {
                    nextSlide(container);
                }
            }

            function prevHandler(event) {
                event.preventDefault();

                var wrapper = container.find('.carousel-boundaries');
                if (wrapper.find('.selected').is(':first-child')) {
                    var selectedPosition = container.find('.carousel-boundaries .selected').index() + (wrapper.children('li').length - 1);
                    nextSlide(container, selectedPosition);
                } else {
                    prevSlide(container);
                }
            }

        });
    }

    function nextSlide(container, n) {
        var visibleSlide = container.find('.carousel-boundaries .selected'); // this is the selected sprite
        if (typeof n === 'undefined') n = visibleSlide.index() + 1; // not sure why we're adding one here
        visibleSlide.removeClass('selected');
        container.find('.carousel-boundaries li') // li list of sprites

            .eq(n)
            .removeClass()
            .addClass('selected') // add selected class to sprite index + 1 of previously selected
            .prevAll() // grabs any li item to the left of new selected sprite
            .removeClass() // removes classes from previous sprites
            .addClass('hide-left') // adds to all previous sprites 'hide-left' class
            .end() // this ends the prevAll traverse and we are now in the context of eq(n)
            .prev() // selects previous sprite from the 'new' current selected sprite
            .removeClass('hide-left')
            .addClass('move-left')
            .end()
            .nextAll()
            .removeClass()
            .addClass('hide-right')
            .end()
            .next()
            .removeClass('hide-right')
            .addClass('move-right');
    }

    function prevSlide(container, n) {
        var visibleSlide = container.find('.carousel-boundaries .selected')
        if (typeof n === 'undefined') n = visibleSlide.index() - 1;
        visibleSlide.removeClass('selected focus-on-left');
        container.find('.carousel-boundaries li')
            .eq(n)
            .removeClass()
            .addClass('selected')
            .nextAll()
            .removeClass()
            .addClass('hide-right')
            .end()
            .next()
            .removeClass()
            .addClass('move-right')
            .end()
            .prevAll()
            .removeClass()
            .addClass('hide-left')
            .end()
            .prev()
            .removeClass('hide-left')
            .addClass('move-left');
    }

    $("a#carousel-ext").on("click touchstart touchend", function (event) {
        event.preventDefault();
    });
});