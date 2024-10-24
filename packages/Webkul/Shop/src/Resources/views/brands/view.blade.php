@push('styles')
    <style>
        .top-collection-container {
            overflow: hidden;
        }

        .top-collection-header {
            padding: 0 15px;
            text-align: center;
            font-size: 70px;
            line-height: 90px;
            color: #060C3B;
            margin-top: 80px;
        }

        .top-collection-header h2 {
            max-width: 595px;
            margin: auto;
            font-family: 'DM Serif Display', serif;
        }

        .top-collection-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
            margin: 60px auto 0;
            padding: 0 90px;
            width: 100%;
        }

        .top-collection-card {
            position: relative;
            text-align: center;
            background: #f9fafb;
            overflow: hidden;
            border-radius: 20px;
        }

        .top-collection-card img {
            border-radius: 16px;
            max-width: 100%;
            transition: transform 300ms ease;
        }

        .top-collection-card:hover img {
            transform: scale(1.05);
        }

        .top-collection-card h3 {
            color: #060C3B;
            font-size: 30px;
            font-family: 'DM Serif Display', serif;
            margin: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 525px) {
            .top-collection-header {
                margin-top: 30px;
                font-size: 32px;
                line-height: 1.5;
            }

            .top-collection-grid {
                gap: 15px;
            }
        }

        @media (max-width: 1024px) {
            .top-collection-grid {
                padding: 0 30px;
            }
        }

        @media (max-width: 640px) {
            .top-collection-grid {
                margin-top: 20px;
            }
        }

        /* Loading Spinner */
        .spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 40px 0;
        }

        .spinner div {
            width: 12px;
            height: 12px;
            margin: 0 5px;
            background-color: #3498db;
            border-radius: 50%;
            animation: bounce 0.6s infinite alternate;
        }

        .spinner div:nth-child(2) {
            animation-delay: 0.2s;
        }

        .spinner div:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(-15px);
            }
        }
    </style>
@endpush

<x-shop::layouts>
    <div class="top-collection-container">
        <div class="top-collection-header">
            <h2>{!! $category->description !!}</h2>
        </div>

        <div class="container top-collection-grid" id="brand-grid">
            @foreach ($brands as $brand)
                <div class="top-collection-card">
                    <a href="/{{ $brand->admin_name . '?' . 'brand=' . $brand->id }}">
                        <img
                            src=""
                            data-src="{{ url('cache/original/' . $brand->swatch_value) }}"
                            class="lazy"
                            width="396" height="396"
                            alt="{{ $brand->label }}"
                            loading="lazy"
                            onload="this.classList.add('loaded')" />
                        <h3>{{ $brand->label }}</h3>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Infinite Scroll Loader -->
        <div class="spinner" id="spinner" style="display: none;">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentPage = 1;
            let isLoading = false;

            // Infinite Scroll Handler
            window.addEventListener('scroll', function () {
                const scrollPosition = window.innerHeight + window.scrollY;
                const threshold = document.body.offsetHeight - 100;

                if (scrollPosition >= threshold && !isLoading) {
                    loadMoreBrands();
                }
            });

            function loadMoreBrands() {
                isLoading = true;
                document.getElementById('spinner').style.display = 'flex';

                const nextPage = ++currentPage;
                const url = `/api/brands?page=${nextPage}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        appendBrands(data.brands);
                        isLoading = false;
                        document.getElementById('spinner').style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error loading more brands:', error);
                        isLoading = false;
                    });
            }

            function appendBrands(brands) {
                const brandGrid = document.getElementById('brand-grid');

                brands.forEach(brand => {
                    const brandCard = document.createElement('div');
                    brandCard.classList.add('top-collection-card');

                    brandCard.innerHTML = `
                        <a href="/${brand.admin_name}?brand=${brand.id}">
                            <img
                                src=""
                                data-src="${brand.swatch_value}"
                                class="lazy"
                                width="396" height="396"
                                alt="${brand.label}"
                                loading="lazy"
                                onload="this.classList.add('loaded')" />
                            <h3>${brand.label}</h3>
                        </a>
                    `;

                    brandGrid.appendChild(brandCard);
                });
            }
        </script>
    @endpush
</x-shop::layouts>
