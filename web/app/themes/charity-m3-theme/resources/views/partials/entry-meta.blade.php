<div class="entry-meta text-sm text-gray-600 mb-4">
  <time class="updated" datetime="{{ get_post_time('c', true) }}">
    {{ get_the_date() }}
  </time>

  <p class="byline author vcard">
    {{ __('By', 'charity-m3') }}
    <a href="{{ get_author_posts_url(get_the_author_meta('ID')) }}" rel="author" class="fn">
      {{ get_the_author() }}
    </a>
  </p>
</div>
