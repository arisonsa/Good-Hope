<article @php(post_class('mb-8'))>
  <header>
    <h2 class="entry-title text-2xl font-bold mb-2">
      <a href="{{ get_permalink() }}">
        {!! $title !!}
      </a>
    </h2>
    @if(get_post_type() === 'post')
      @include('partials.entry-meta')
    @endif
  </header>

  <div class="entry-summary prose lg:prose-xl">
    @php(the_excerpt())
  </div>
</article>
