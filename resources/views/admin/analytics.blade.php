<x-admin.layout title="DotBio - Analytics" x-data="analyticsDashboard({{ $profile->views_count }}, {{ $profile->links->sum('clicks_count') }})">
    <x-slot name="head">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/jsvectormap"></script>
        <script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <style>
            .dotbio-card {
                background-color: #121017;
                border-radius: 20px;
            }
            .jvm-container {
                width: 100%;
                height: 280px;
            }
            .jvm-tooltip {
                background-color: #17151c !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                border-radius: 12px !important;
                padding: 8px 12px !important;
                font-family: 'Outfit', sans-serif !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
            }
        </style>
    </x-slot>

    <div class="space-y-8">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold font-space flex items-center gap-3">
                <i class="fa-solid fa-chart-simple text-purple-400 text-xl"></i>
                Profile Analytics
            </h1>
            <p class="text-xs text-zinc-400">Track views, clicks, devices, and traffic sources for your profile.</p>
        </div>

        <x-admin.card class="p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 select-none">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-zinc-300">Time Range</span>
                    <span class="bg-purple-600/20 text-purple-400 border border-purple-500/30 text-[11px] px-2.5 py-0.5 rounded-full font-medium">Last updated just now</span>
                </div>
                
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="bg-[#181621] hover:bg-[#201d2c] border border-white/10 rounded-xl px-4 py-2 flex items-center gap-3 text-xs text-zinc-200 transition cursor-pointer">
                        <i class="fa-regular fa-calendar text-purple-400"></i>
                        <span x-text="selectedRangeLabel" class="font-medium"></span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-zinc-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open" 
                         @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-44 bg-[#181621] border border-white/10 rounded-2xl shadow-2xl py-1.5 z-50 overflow-hidden">
                        <button @click="setTimeRange('3days'); open = false" class="w-full px-4 py-2 text-left text-xs hover:bg-purple-600/20 hover:text-purple-300 transition flex items-center justify-between cursor-pointer" :class="{ 'text-purple-400 font-semibold bg-purple-600/10': range === '3days' }">
                            <span>Last 3 days</span>
                            <i x-show="range === '3days'" class="fa-solid fa-check text-xs"></i>
                        </button>
                        <button @click="setTimeRange('7days'); open = false" class="w-full px-4 py-2 text-left text-xs hover:bg-purple-600/20 hover:text-purple-300 transition flex items-center justify-between cursor-pointer" :class="{ 'text-purple-400 font-semibold bg-purple-600/10': range === '7days' }">
                            <span>Last 7 days</span>
                            <i x-show="range === '7days'" class="fa-solid fa-check text-xs"></i>
                        </button>
                        <button @click="setTimeRange('30days'); open = false" class="w-full px-4 py-2 text-left text-xs hover:bg-purple-600/20 hover:text-purple-300 transition flex items-center justify-between cursor-pointer" :class="{ 'text-purple-400 font-semibold bg-purple-600/10': range === '30days' }">
                            <span>Last 30 days</span>
                            <i x-show="range === '30days'" class="fa-solid fa-check text-xs"></i>
                        </button>
                    </div>
                </div>
        </x-admin.card>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-admin.card class="p-5 space-y-2">
                    <div class="flex items-center justify-between text-zinc-400 text-xs font-medium">
                        <span>Total Link Clicks</span>
                        <i class="fa-solid fa-arrow-pointer text-zinc-500"></i>
                    </div>
                    <p class="text-2xl font-bold font-space text-white" x-text="totalClicks"></p>
                    <p class="text-[11px] text-zinc-500" x-text="'Past ' + (range === '3days' ? '3' : (range === '7days' ? '7' : '30')) + ' days'"></p>
                </x-admin.card>

                <x-admin.card class="p-5 space-y-2">
                    <div class="flex items-center justify-between text-zinc-400 text-xs font-medium">
                        <span>Click Rate</span>
                        <i class="fa-solid fa-percent text-zinc-500"></i>
                    </div>
                    <p class="text-2xl font-bold font-space text-white">0.00%</p>
                    <p class="text-[11px] text-zinc-500" x-text="'Past ' + (range === '3days' ? '3' : (range === '7days' ? '7' : '30')) + ' days'"></p>
                </x-admin.card>

                <x-admin.card class="p-5 space-y-2">
                    <div class="flex items-center justify-between text-zinc-400 text-xs font-medium">
                        <span>Profile Views</span>
                        <i class="fa-solid fa-eye text-zinc-500"></i>
                    </div>
                    <p class="text-2xl font-bold font-space text-white" x-text="currentPeriodViews"></p>
                    <p class="text-[11px] text-emerald-400" x-text="'+' + currentPeriodViews + ' views in selected period'"></p>
                </x-admin.card>

                <x-admin.card class="p-5 space-y-2">
                    <div class="flex items-center justify-between text-zinc-400 text-xs font-medium">
                        <span>Avg Daily Views</span>
                        <i class="fa-solid fa-chart-line text-zinc-500"></i>
                    </div>
                    <p class="text-2xl font-bold font-space text-white" x-text="avgDailyViews"></p>
                    <p class="text-[11px] text-zinc-500" x-text="'Past ' + (range === '3days' ? '3' : (range === '7days' ? '7' : '30')) + ' days'"></p>
                </x-admin.card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <x-admin.card class="lg:col-span-2 p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-200" x-text="'Profile views past ' + (range === '3days' ? '3' : (range === '7days' ? '7' : '30')) + ' days'"></h2>
                    </div>

                    <div class="pt-4">
                        <div id="apexViewsChart"></div>
                    </div>
                </x-admin.card>

                <x-admin.card class="p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-200" x-text="'Visitor devices past ' + (range === '3days' ? '3' : (range === '7days' ? '7' : '30')) + ' days'"></h2>
                    </div>
                    
                    <div class="py-4">
                        <div id="apexDevicesChart"></div>
                    </div>

                    <div class="flex items-center justify-center gap-4 text-xs text-zinc-400 pt-2">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#9333ea]"></span> Desktop</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#a855f7]"></span> Mobile</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#c084fc]"></span> Tablet</span>
                    </div>
                </x-admin.card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-admin.card class="p-6 space-y-4">
                    <h2 class="text-sm font-semibold text-zinc-200">Most Clicked Socials</h2>

                    <div class="space-y-2">
                        @forelse($profile->links->sortByDesc('clicks_count') as $link)
                            <div class="p-3 bg-[#09080d] border border-white/5 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="{{ $link->icon_class }} text-purple-400 text-base"></i>
                                    <span class="text-xs font-semibold text-zinc-200">{{ $link->title }}</span>
                                </div>
                                <span class="text-xs font-mono text-zinc-400 font-semibold">{{ number_format($link->clicks_count) }} clicks</span>
                            </div>
                        @empty
                            <div class="py-8 text-center space-y-1">
                                <p class="text-xs font-semibold text-zinc-400">No link clicks yet</p>
                                <p class="text-[11px] text-zinc-500">Share your DotBio profile on social media to get clicks!</p>
                            </div>
                        @endforelse
                    </div>
                </x-admin.card>

                <x-admin.card class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-zinc-200">Traffic Sources</h2>
                        <i class="fa-solid fa-circle-info text-zinc-500 text-xs" title="Domain referrers of your visitors"></i>
                    </div>

                    <div class="space-y-2">
                        <div class="p-3 bg-[#09080d] border border-white/5 rounded-xl flex items-center justify-between">
                            <span class="text-xs font-medium text-zinc-300">DotBio Direct</span>
                            <span class="text-xs font-mono text-zinc-400 font-semibold" x-text="totalViews + ' clicks'"></span>
                        </div>
                    </div>
                </x-admin.card>
        </div>

            <x-admin.card class="p-6 space-y-6">
    <h2 class="text-sm font-semibold text-zinc-200">Top Countries with Most Views</h2>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">
        <div class="space-y-3 max-h-[280px] overflow-y-auto pr-1">
            @forelse($topCountries as $country)
                <div class="p-3.5 bg-[#09080d] border border-white/5 rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="https://flagcdn.com/w40/{{ strtolower($country['code']) }}.png" 
                             srcset="https://flagcdn.com/w80/{{ strtolower($country['code']) }}.png 2x" 
                             width="24" height="16" 
                             alt="{{ $country['name'] }}" 
                             class="rounded-sm shadow-sm object-cover">
                        <div>
                            <p class="text-xs font-bold text-white">{{ $country['name'] }}</p>
                            <p class="text-[10px] text-zinc-500">
                                {{ number_format(($country['views'] / max($profile->views_count, 1)) * 100, 1) }}% of total views
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold text-purple-400">{{ number_format($country['views']) }} views</span>
                </div>
            @empty
                <div class="p-3.5 bg-[#09080d] border border-white/5 rounded-2xl flex items-center gap-3">
                    <i class="fa-solid fa-globe text-zinc-500 text-sm"></i>
                    <div>
                        <p class="text-xs font-bold text-white">Country tracking unavailable</p>
                        <p class="text-[10px] text-zinc-500">New visits will populate this panel once the visitor country can be resolved from IP.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="lg:col-span-2 relative flex items-center justify-center">
            <div id="worldMap" class="jvm-container"></div>
        </div>
    </div>
</x-admin.card>
    </div>

    <x-slot name="scripts">
        <script>
        let viewsChartInstance = null;

        document.addEventListener('alpine:init', () => {
            Alpine.data('analyticsDashboard', (viewsCount, clicksCount) => ({
                range: '3days',
                totalViews: viewsCount,
                totalClicks: clicksCount,

                get selectedRangeLabel() {
                    if (this.range === '3days') return 'Last 3 days';
                    if (this.range === '7days') return 'Last 7 days';
                    return 'Last 30 days';
                },

                get currentPeriodViews() {
                    if (this.range === '3days') {
                        return @json(array_sum($views3Days));
                    } else if (this.range === '7days') {
                        return @json(array_sum($views7Days));
                    }
                    return @json(array_sum($views30Days));
                },

                get avgDailyViews() {
                    const days = this.range === '3days' ? 3 : (this.range === '7days' ? 7 : 30);
                    return (this.currentPeriodViews / days).toFixed(1);
                },

                setTimeRange(newRange) {
                    this.range = newRange;
                    this.updateViewsChart();
                },

                updateViewsChart() {
                    if (!viewsChartInstance) return;

                    let categories = [];
                    let seriesData = [];

                    const dates3Days = @json($dates3Days);
                    const views3Days = @json($views3Days);

                    const dates7Days = @json($dates7Days);
                    const views7Days = @json($views7Days);

                    const dates30Days = @json($dates30Days);
                    const views30Days = @json($views30Days);

                    if (this.range === '3days') {
                        categories = dates3Days;
                        seriesData = views3Days;
                    } else if (this.range === '7days') {
                        categories = dates7Days;
                        seriesData = views7Days;
                    } else {
                        categories = dates30Days;
                        seriesData = views30Days;
                    }

                    viewsChartInstance.updateOptions({
                        xaxis: { categories: categories },
                        series: [{ name: 'Profile Views', data: seriesData }]
                    });
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', () => {
            const countryViews = @json($countryViews);
            const selectedCountries = @json(array_keys($countryViews));

            new jsVectorMap({
                selector: '#worldMap',
                map: 'world',
                draggable: false,
                zoomButtons: false,
                zoomOnScroll: false,
                regionStyle: {
                    initial: { fill: '#1c1826', fillOpacity: 1, stroke: '#2b233a', strokeWidth: 0.5 },
                    hover: { fill: '#a855f7' },
                    selected: { fill: '#a855f7' }
                },
                selectedRegions: selectedCountries,
                visualizeData: {
                    scale: ['#1c1826', '#a855f7'],
                    values: countryViews
                },
                onRegionTooltipShow(event, tooltip, code) {
                    const countryName = tooltip.text();
                    const views = countryViews[code] ?? 0;
                    tooltip.css({ display: 'block' });
                    tooltip.text(`
                        <div class="text-center space-y-0.5">
                            <div class="text-xs font-bold text-white">${countryName}</div>
                            <div class="text-[11px] font-semibold text-purple-400">${views} views</div>
                        </div>
                    `, true);
                }
            });

            const devicesChart = new ApexCharts(document.querySelector("#apexDevicesChart"), {
                series: [1, 0, 0],
                labels: ['Desktop', 'Mobile', 'Tablet'],
                chart: { type: 'donut', height: 240, sparkline: { enabled: true } },
                colors: ['#9333ea', '#a855f7', '#c084fc'],
                stroke: { width: 0 },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: { show: true, fontSize: '12px', color: '#a1a1aa', offsetY: -10 },
                                value: { show: true, fontSize: '18px', fontWeight: 700, color: '#ffffff', offsetY: 5, formatter: () => '1 Visitors' },
                                total: { show: true, label: 'Total', color: '#e4e4e7', fontSize: '14px', formatter: () => '1 Visitors' }
                            }
                        }
                    }
                },
                tooltip: { enabled: false },
                legend: { show: false }
            });
            devicesChart.render();

            viewsChartInstance = new ApexCharts(document.querySelector("#apexViewsChart"), {
                series: [{
                    name: 'Profile Views',
                    data: @json($views3Days)
                }],

                chart: { type: 'area', height: 250, toolbar: { show: false }, sparkline: { enabled: false }, background: 'transparent' },
                colors: ['#a855f7'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.05, stops: [0, 90, 100] }
                },
                markers: { size: 0, hover: { size: 6 } },
                xaxis: {
                    categories: @json($dates3Days),
                    labels: { style: { colors: '#71717a', fontSize: '11px' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },

                yaxis: { labels: { style: { colors: '#71717a', fontSize: '11px' } } },
                grid: { show: false },
                tooltip: { theme: 'dark' }
            });
            viewsChartInstance.render();
        });
        </script>
    </x-slot>
</x-admin.layout>
