<footer class="footer footer-style1">
    <div class="container">
        <div class="footer-main">
            <div class="footer-logo">
                <div class="logo-footer">
                    <img src="/images/logo_horizontal.png" alt="">
                </div>
                <p class="des-footer">
                </p>
                <ul class="footer-info">
                    <li class="flex-three">
                        <p>{{env('EMAIL')}}</p>
                    </li>
                    <li class="flex-three">
                        <p><a href="tel:{{env('PHONE')}}">{{env('PHONE')}}</a></p>
                    </li>
                </ul>
            </div>
            <div class="footer-service">
                <h5 class="title">Services</h5>
                <ul class="footer-menu">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Gallery</a></li>
                    <li><a href="#">Blog Insights</a></li>
                </ul>
            </div>
            <div class="footer-gallery">
                <h5 class="title">Gallery</h5>

                <div class="gallery-img">
                    <img src="/images/gallery/gl1.jpg" alt="image gallery">
                    <a href="/images/gallery/gl2.jpg" data-fancybox="gallery">
                        <img src="/images/gallery/gl2.jpg" alt="image gallery">
                    </a>
                    <a href="/images/gallery/gl3.jpg" data-fancybox="gallery">
                        <img src="/images/gallery/gl3.jpg" alt="image gallery">
                    </a>
                    <a href="/images/gallery/gl4.jpg" data-fancybox="gallery">
                        <img src="/images/gallery/gl4.jpg" alt="image gallery">
                    </a>
                    <a href="/images/gallery/gl5.jpg" data-fancybox="gallery">
                        <img src="/images/gallery/gl5.jpg" alt="image gallery">
                    </a>
                    <a href="/images/gallery/gl6.jpg" data-fancybox="gallery">
                        <img src="/images/gallery/gl6.jpg" alt="image gallery">
                    </a>
                </div>
            </div>
            <div class="footer-newsletter">
                <h5 class="title">Newsletter</h5>
                <form action="/subscribe" id="footer-form" method="post" class="px-0" style="width: 100%;">
                    @csrf
                    <div class="input-wrap flex-three">
                        <input type="email" class="mr-text" required name="email" placeholder="Enter Email Address">
                        <button type="submit" class="mr-btn-success"><i class="fa fa-paper-plane"></i></button>
                    </div>
                    <div class="check-form flex-three" style="color:white">
                        <i class="fa fa-check-circle"></i>I agree to all your terms and policies
                    </div>
                </form>
                <ul class="social-ft flex-three">
                    <li><a href="#"> <i class="fa-brands fa-lg fa-instagram"></i> </a></li>
                    <li><a href="#"> <i class="fa-brands fa-lg fa-vk"></i> </a></li>
                    <li><a href="#"> <i class="fa-brands fa-lg fa-x"></i> </a></li>
                    <li><a href="#"> <i class="fa-brands fa-lg fa-facebook"></i> </a></li>
                </ul>
            </div>
        </div>

        <div class="row footer-bottom">
            <div class="col-md-12">
                <p class="copy-right">Copyright © <?php echo now()->format('Y'); ?>. All Rights Reserved</p>
            </div>
        </div>
    </div>
</footer>
