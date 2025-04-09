<?php 
/*
 * @Theme Name:WebStack
 * @Theme URI:https://github.com/owen0o0/WebStack
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2019-02-22 21:26:02
 * @LastEditors: iowen
 * @LastEditTime: 2023-04-24 00:42:32
 * @FilePath: \WebStack\footer.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$_icp = '';
if(io_get_option('icp')){
    $_icp .= '<a href="https://beian.miit.gov.cn/" target="_blank" rel="link noopener">' . io_get_option('icp') . '</a>&nbsp;';
}
if ($police_icp = io_get_option('police_icp')) {
    if (preg_match('/\d+/', $police_icp, $arr)) {
        $_icp .= ' <a href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=' . $arr[0] . '" target="_blank" class="'.$class.'" rel="noopener">' . $police_icp . '</a>&nbsp;';
    }
}
?>
            <footer class="main-footer sticky footer-type-1">
                <div class="go-up">
                    <a href="#" rel="go-top">
                        <i class="fa fa-angle-up"></i>
                    </a>
                </div>
                <div class="footer-inner">
                    <!---请保留版权说明，谢谢---->
                    <div class="footer-text">
                        Copyright © <?php echo date('Y') ?> <?php bloginfo('name'); ?> <?php echo $_icp ?>
                        &nbsp;&nbsp;Design by <a href="https://github.com/WebStackPage/WebStackPage.github.io" target="_blank"><strong>Webstack</strong></a>&nbsp;&nbsp;Modify by <a href="https://github.com/owen0o0/WebStack" target="_blank"><strong>iowen</strong></a>
                    </div>
                    <!---请保留版权说明，谢谢---->
                </div>
            </footer>
        </div>
    </div>
<?php if (is_home() || is_front_page()): ?>
    <script type="text/javascript">
    $(document).ready(function() {
        setTimeout(function () { 
            if($('a.smooth[href="'+window.location.hash+'"]')[0]){
                $('a.smooth[href="'+window.location.hash+'"]').click();
            } else if(window.location.hash != ''){
                $("html, body").animate({
                    scrollTop: $(window.location.hash).offset().top - 80
                }, {
                    duration: 500,
                    easing: "swing"
                });
            }
        }, 300);
        $(document).on('click', '.has-sub', function(){
            var _this = $(this)
            if(!$(this).hasClass('expanded')) {
                setTimeout(function(){
                    _this.find('ul').attr("style","")
                }, 300);
            } else {
                $('.has-sub ul').each(function(id,ele){
                    var _that = $(this)
                    if(_this.find('ul')[0] != ele) {
                        setTimeout(function(){
                            _that.attr("style","")
                        }, 300);
                    }
                })
            }
        })
        $('.user-info-menu .hidden-xs').click(function(){
            if($('.sidebar-menu').hasClass('collapsed')) {
                $('.has-sub.expanded > ul').attr("style","")
            } else {
                $('.has-sub.expanded > ul').show()
            }
        })
        $("#main-menu li ul li").click(function() {
            $(this).siblings('li').removeClass('active'); // 删除其他兄弟元素的样式
            $(this).addClass('active'); // 添加当前元素的样式
        });
        $("a.smooth").click(function(ev) {
            ev.preventDefault();
            if($("#main-menu").hasClass('mobile-is-visible') != true)
                return;
            public_vars.$mainMenu.add(public_vars.$sidebarProfile).toggleClass('mobile-is-visible');
            ps_destroy();
            $("html, body").animate({
                scrollTop: $($(this).attr("href")).offset().top - 80
            }, {
                duration: 500,
                easing: "swing"
            });
        });
        return false;
    });

    var href = "";
    var pos = 0;
    $("a.smooth").click(function(e) {
        e.preventDefault();
        if($("#main-menu").hasClass('mobile-is-visible') === true)
            return;
        $("#main-menu li").each(function() {
            $(this).removeClass("active");
        });
        $(this).parent("li").addClass("active");
        href = $(this).attr("href");
        pos = $(href).position().top - 100;
        $("html,body").animate({
            scrollTop: pos
        }, 500);
    });
    </script>
<?php endif; ?>
<?php wp_footer(); ?>
<!-- 自定义代码 -->
<?php echo io_get_option('code_2_footer');?>
<!-- end 自定义代码 -->

<!-- Back to Top Enhancement Styles -->
<style>
.go-up {
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
  /* Add position styles if not already defined by the theme, e.g.: */
  /* position: fixed; */
  /* bottom: 20px; */
  /* right: 20px; */
  /* z-index: 1000; */
}
.go-up.is-visible {
  opacity: 1;
  visibility: visible;
}
</style>

<!-- Back to Top Enhancement Script -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
  const goTopButton = document.querySelector('a[rel="go-top"]');
  const goTopContainer = document.querySelector('.go-up'); // Get the container

  if (goTopButton && goTopContainer) {
    const scrollThreshold = 300; // Pixels to scroll before showing the button

    // Function to check scroll position and toggle visibility
    const checkScroll = () => {
      if (window.pageYOffset > scrollThreshold) {
        goTopContainer.classList.add('is-visible');
      } else {
        goTopContainer.classList.remove('is-visible');
      }
    };

    // Initial check in case the page loads already scrolled down
    checkScroll();

    // Listen for scroll events
    window.addEventListener('scroll', checkScroll);

    // Listen for click events on the button
    goTopButton.addEventListener('click', function(event) {
      event.preventDefault(); // Prevent default anchor behavior
      window.scrollTo({
        top: 0,
        behavior: 'smooth' // Smooth scroll to top
      });
    });
  } else {
    // Optional: Log an error if the button or container isn't found
    // console.error('Back to Top button (a[rel="go-top"]) or container (.go-up) not found.');
  }
});
</script>

</body>
</html>