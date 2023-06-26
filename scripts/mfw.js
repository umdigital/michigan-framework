(function($){
    $(document).ready(function(){
        if( $('HTML').hasClass('lte-ie8') ) {
            // get grid columns
            var gridCols = 12;
            if( $('body').attr('class').replace(/^.*?mfwGrid-([0-9]{1,2}).*?$/, '$1') ) {
                gridCols = $('body').attr('class').replace(/^.*?mfwGrid-([0-9]{1,2}).*?$/, '$1');
            }

            // remove smaller sized classes
            // rem small-* if medium-* or large-* class exists
            // rem medium-* if large-* class exists
            for (var c = 1; c <= gridCols; c++) {
                $('.small-' + c + '[class*="medium"]').removeClass('small-' + c);
                $('.small-' + c + '[class*="large"]').removeClass('small-' + c);
                $('.medium-' + c + '[class*="large"]').removeClass('medium-' + c);
                //Perform any other column-specific adjustments your design may require here
            }

            $('.show-for-small').remove();
            $('.hide-for-small').removeClass('hide-for-small');
            $('.top-bar-section UL').css({ width: 'inherit' });
            $('.top-bar-section UL LI').css({ float: 'left' });
            $('.top-bar').css({ height: 'inherit' });

            $('img').removeAttr('height').removeAttr('width');
        }

        /*
        if( $.browser.msie && (parseInt( $.browser.version ) == 8) ) {
            // IE font race condition fix
            var head = document.getElementsByTagName('head')[0],
                style = document.createElement('style');
                style.type = 'text/css';
                style.styleSheet.cssText = ':before,:after{content:none !important';
                head.appendChild( style );
                setTimeout(function(){
                head.removeChild( style );
            }, 0);
        }
        */

        // placeholder support for older browsers
        $('input').placeholder();

        // IE HTML5 VIDEO DOWNLOAD CHECK
        // ie can fail some partial downloads of video files
         $('video').on('error', function(){
            // if we get code 4, formats are not supported
            // anything else try and reload it, up to 4 times
            if( ($(this).get(0).error.code != 4) && ($(this).data('attempt') < 4) ) {
                $(this).get(0).load();

                var attempt = 0;
                if( $(this).data('attempt') > 0 ) {
                    attempt = parseInt( $(this).data('attempt') );
                }
                $(this).data('attempt', (attempt + 1) );
            }
        });

        // wait for assets to load
        $(window).one('load', function(){
            // RESPONSIVE MENU
            var mainMenuWrap = $('#zone-header-menu .mfwMenu');
            var mainMenuList = mainMenuWrap.find('.header-menu');

            var totalWidth = 0;
            mainMenuList.find('> li').each(function(){
                totalWidth += $(this).outerWidth( true );
            });

            $(window).resize(function(){
                if( mainMenuWrap.width() <= totalWidth ) {
                    if( !mainMenuWrap.hasClass('responsive') ) {
                        mainMenuList.hide();
                        mainMenuWrap.addClass('responsive');
                    }
                }
                else {
                    if( mainMenuWrap.hasClass('responsive') ) {
                        mainMenuWrap.removeClass('responsive');
                        mainMenuList.show();
                    }
                }
            }).trigger('resize');

            $(document).on('click', '#zone-header-menu .hamburger-header', function( event ){
                event.preventDefault();

                mainMenuList.slideToggle();
            });


            // ACCORDION
            $('.mfw-accordion > input[type="checkbox"]').change(function(){
                var thisContent = $(this).parent().find('> .mfw-accordion-content-wrap');

                // if accordion should be shown, set maxheight
                if( $(this).is(':checked') ) {
                    $(this).parent().find('> label > span').attr(
                        'aria-expanded', 'true'
                    );

                    thisContent.css({
                        'maxHeight': thisContent.data('maxheight')
                    });

                    if( typeof gtag != 'undefined' ) {
                        gtag( 'event', 'click', {
                            'event_category': 'Accordion',
                            'event_label'   : $(this).closest('.mfw-accordion').find('.mfw-accordion-title').text()
                        });
                    }
                }
                // reset maxheight to default
                else {
                    $(this).parent().find('> label > span').attr(
                        'aria-expanded', 'false'
                    );

                    thisContent.css({
                        'maxHeight': ''
                    });
                }
            });

            // handle keyboard accessibility
            $('.mfw-accordion > input[type="checkbox"]').keydown(function( event ){
                if( event.which === 13 ) {
                    event.preventDefault();

                    $(this).trigger('click');
                }
            });
            $('.mfw-accordion > label').keydown(function( event ){
                console.log( 'label' );
                if( (event.which === 13) || (event.which === 32) ) {
                    event.preventDefault();

                    $(this).parent().find('> input[type="checkbox"]').trigger('click');
                }
            });

            // determine each accordion contents maxheight
            var recalcAccordions = function(){
                $('.mfw-accordion').each(function(){
                    var thisContent = $(this).find('> .mfw-accordion-content-wrap');

                    thisContent.removeClass('transition')
                        .css('maxHeight','inherit')
                        .data('maxheight', thisContent.height() * 1.1 )
                        .css('maxHeight','')
                        .addClass('transition');

                    if( $(this).find('> input[type="checkbox"]').is(':checked') ) {
                        thisContent.css('maxHeight', thisContent.data('maxheight') );
                    }
                });
            };
            recalcAccordions();

            $(window).resize(function(){
                recalcAccordions()
            });
        }).trigger('load');
    });
}(jQuery));
