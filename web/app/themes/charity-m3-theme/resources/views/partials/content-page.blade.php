<div class="content-area"> {{-- Basic wrapper, styling from main.scss --}}
  @php(the_content())
</div>

{!! wp_link_pages([
  'echo' => 0,
  'before' => '<nav class="page-nav"><p>' . __('Pages:', 'charity-m3'),
  'after' => '</p></nav>'
]) !!}
