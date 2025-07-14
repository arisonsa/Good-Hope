<footer class="content-info">
  <div class="footer-container">
    <div class="footer-widgets">
      @if (is_active_sidebar('footer-1'))
        <div class="footer-widget-col footer-widget-col-1">
          @php(dynamic_sidebar('footer-1'))
        </div>
      @endif
      @if (is_active_sidebar('footer-2'))
        <div class="footer-widget-col footer-widget-col-2">
          @php(dynamic_sidebar('footer-2'))
        </div>
      @endif
      @if (is_active_sidebar('footer-3'))
        <div class="footer-widget-col footer-widget-col-3">
          @php(dynamic_sidebar('footer-3'))
        </div>
      @endif
    </div>

    <div class="footer-copyright">
      @if (is_active_sidebar('footer-copyright'))
          @php(dynamic_sidebar('footer-copyright'))
      @else
        <p>&copy; {{ date('Y') }} {{ $siteName ?? get_bloginfo('name') }}. All Rights Reserved.</p>
      @endif
    </div>
  </div>
</footer>
