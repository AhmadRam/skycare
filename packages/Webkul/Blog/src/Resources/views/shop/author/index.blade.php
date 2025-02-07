@php
    $channel = core()->getCurrentChannel();
@endphp


{{-- SEO Meta Content --}}
@push('meta')
    <meta name="title" content="{{ $blog_seo_meta_title ?? ($channel->home_seo['meta_title'] ?? '') }}" />

    <meta name="description" content="{{ $blog_seo_meta_keywords ?? ($channel->home_seo['meta_description'] ?? '') }}" />

    <meta name="keywords" content="{{ $blog_seo_meta_description ?? ($channel->home_seo['meta_keywords'] ?? '') }}" />
@endPush

<x-shop::layouts>
    {{-- Page Title --}}
    <x-slot:title>
        {{ __('blog::app.author.posted-by', ['author' => $author_data->author]) }}
    </x-slot>

    @push('styles')
        @include ('blog::custom-css.custom-css')
    @endpush

    <div class="main">

        <div>
            <div class="row col-12 remove-padding-margin"><!---->
                <div id="home-right-bar-container" class="col-12 no-padding content">
                    <div class="container-right row no-margin col-12 no-padding">
                        <div id="blog" class="container mt-5">
                            <div class="full-content-wrapper">
                                <div class="col-lg-12">
                                    <h1 class="mb-3 page-title">
                                        {{ __('blog::app.author.posted-by', ['author' => $author_data->author]) }}</h1>
                                </div>
                                <div class="flex flex-wrap grid-wrap">

                                    <div class="column-9">

                                        @if (!empty($blogs) && count($blogs) > 0)

                                            <div class="flex flex-wrap blog-grid-list">

                                                @foreach ($blogs as $blog)
                                                    <div class="blog-post-item">
                                                        <div class="blog-post-box">
                                                            <div class="card mb-5">
                                                                <div class="blog-grid-img"><img
                                                                        src="{{ '/storage/' . (isset($blog->src) && !empty($blog->src) && !is_null($blog->src) ? $blog->src : 'placeholder-thumb.jpg') }}"
                                                                        alt="{{ $blog->name }}" class="card-img-top">
                                                                </div>
                                                                <div class="card-body">
                                                                    <h2 class="card-title"><a
                                                                            href="{{ route('shop.article.view', [$blog->slug]) }}">{{ $blog->name }}</a>
                                                                    </h2>
                                                                    <div class="post-meta">
                                                                        <p>
                                                                            @php
                                                                                $date = \Carbon\Carbon::createFromFormat(
                                                                                    'Y-m-d H:i:s',
                                                                                    $blog->created_at,
                                                                                );
                                                                                $locale = app()->getLocale(); // Get the current locale

                                                                                if ($locale === 'ar') {
                                                                                    // Format for Arabic
                                                                                    $formattedDate = $date->format(
                                                                                        'j M, Y',
                                                                                    );
                                                                                    $formattedDate = str_replace(
                                                                                        [
                                                                                            'Jan',
                                                                                            'Feb',
                                                                                            'Mar',
                                                                                            'Apr',
                                                                                            'May',
                                                                                            'Jun',
                                                                                            'Jul',
                                                                                            'Aug',
                                                                                            'Sep',
                                                                                            'Oct',
                                                                                            'Nov',
                                                                                            'Dec',
                                                                                        ],
                                                                                        [
                                                                                            'يناير',
                                                                                            'فبراير',
                                                                                            'مارس',
                                                                                            'أبريل',
                                                                                            'مايو',
                                                                                            'يونيو',
                                                                                            'يوليو',
                                                                                            'أغسطس',
                                                                                            'سبتمبر',
                                                                                            'أكتوبر',
                                                                                            'نوفمبر',
                                                                                            'ديسمبر',
                                                                                        ],
                                                                                        $formattedDate,
                                                                                    );
                                                                                } else {
                                                                                    // Format for English
                                                                                    $formattedDate = $date->format(
                                                                                        'M j, Y',
                                                                                    );
                                                                                }
                                                                            @endphp
                                                                            {{ $formattedDate }}
                                                                            {{ __('blog::app.author.by') }}
                                                                            {{ __('blog::app.home.skycare') }}
                                                                            {{-- @if ((int) $show_author_page == 1)
                                                                                <a
                                                                                    href="{{ route('shop.blog.author.index', [$blog->author_id]) }}">{{ $blog->author }}</a>
                                                                            @else
                                                                                <a>{{ $blog->author }}</a>
                                                                            @endif --}}
                                                                        </p>
                                                                    </div>

                                                                    @if (!empty($blog->assign_categorys) && count($blog->assign_categorys) > 0)
                                                                        <div class="post-categories">
                                                                            <p>
                                                                                @foreach ($blog->assign_categorys as $assign_category)
                                                                                    <a href="{{ route('shop.blog.category.index', [$assign_category->slug]) }}"
                                                                                        class="cat-link">{{ $assign_category->name }}</a>
                                                                                @endforeach
                                                                            </p>
                                                                        </div>
                                                                    @endif

                                                                    <div class="card-text text-justify">
                                                                        {!! $blog->short_description !!}
                                                                    </div>
                                                                </div>
                                                                <div class="card-footer">
                                                                    <a href="{{ route('shop.article.view', [$blog->slug]) }}"
                                                                        class="text-uppercase btn-text-link">{{ __('blog::app.author.read-more') }}
                                                                        ></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach

                                                <div class="w-full col-lg-12 mt-5 mb-5">
                                                    {!! $blogs->links() !!}
                                                </div>

                                            </div>
                                        @else
                                            <div class="post-not-available">
                                                {{ __('blog::app.author.no-post-published') }}</div>
                                        @endif

                                    </div>

                                    <div class=" column-3 blog-sidebar">
                                        <div class="row">
                                            <div class="col-lg-12 mb-4 categories">
                                                <h3>{{ __('blog::app.author.categories') }}</h3>
                                                <ul class="list-group">
                                                    @foreach ($categories as $category)
                                                        <li><a href="{{ route('shop.blog.category.index', [$category->slug]) }}"
                                                                class="list-group-item list-group-item-action">
                                                                <span>{{ $category->name }}</span>
                                                                @if ((int) $show_categories_count == 1)
                                                                    <span
                                                                        class="badge badge-pill badge-primary">{{ $category->assign_blogs }}</span>
                                                                @endif
                                                            </a></li>
                                                    @endforeach
                                                </ul>

                                                <div class="tags-part">
                                                    <h3>{{ __('blog::app.author.tags') }}</h3>
                                                    <div class="tag-list">
                                                        @foreach ($tags as $tag)
                                                            <a href="{{ route('shop.blog.tag.index', [$tag->slug]) }}"
                                                                role="button"
                                                                class="btn btn-primary btn-lg">{{ $tag->name }}
                                                                @if ((int) $show_tags_count == 1)
                                                                    <span
                                                                        class="badge badge-light">{{ $tag->count }}</span>
                                                                @endif
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-shop::layouts>
