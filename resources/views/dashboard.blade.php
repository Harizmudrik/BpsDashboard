<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPT-BPS — Dashboard Tingkat Pengangguran Terbuka</title>
    <meta name="description" content="Dashboard Visualisasi Tingkat Pengangguran Terbuka Indonesia berdasarkan data BPS">
    <link rel="icon" type="image/svg+xml" href="{{ asset('LOGOBPS.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('LOGOBPS.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none!important}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f0f2f5;color:#333;min-height:100vh}

        /* Header gradient */
        .header-bg{background:linear-gradient(135deg,#1F3C88 0%,#2C5AA0 60%,#3FA9F5 100%)}
        .nav-inner{max-width:1360px;margin:0 auto;padding:16px 24px}

        /* Cards */
        .card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04);transition:all .25s ease}
        .card:hover{box-shadow:0 8px 25px rgba(31,60,136,0.1);transform:translateY(-2px)}

        /* Stat card accent */
        .stat-card{position:relative;overflow:hidden}
        .stat-card::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;border-radius:12px 0 0 12px}
        .stat-card.blue::before{background:#1F3C88}
        .stat-card.red::before{background:#e74c3c}
        .stat-card.green::before{background:#4CAF50}
        .stat-card.cyan::before{background:#3FA9F5}

        /* Layout */
        .container{max-width:1360px;margin:0 auto;padding:0 24px}
        .grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
        .grid-3-1{display:grid;grid-template-columns:2fr 1fr;gap:20px}
        .grid-1{display:grid;grid-template-columns:1fr;gap:20px}

        /* Filters */
        .filter-select{background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);border-radius:8px;padding:8px 12px;font-size:13px;font-family:inherit;outline:none;cursor:pointer;transition:all .2s}
        .filter-select:focus{border-color:#fff;background:rgba(255,255,255,0.25)}
        .filter-select option{color:#333;background:#fff}

        .btn-export{background:#4CAF50;color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;font-family:inherit;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s}
        .btn-export:hover{background:#43a047;transform:translateY(-1px)}

        /* Table */
        .data-table{width:100%;border-collapse:collapse;font-size:13px}
        .data-table th{text-align:left;padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#666;border-bottom:2px solid #e8eaed;background:#fafbfc}
        .data-table td{padding:10px 14px;border-bottom:1px solid #f0f0f0}
        .data-table tbody tr:hover{background:#E3F2FD}
        .data-table th:nth-child(5),.data-table td:nth-child(5){text-align:right}

        .badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-high{background:#fde8e8;color:#e74c3c}
        .badge-mid{background:#fef3cd;color:#d68910}
        .badge-low{background:#d4edda;color:#28a745}

        .search-input{border:1px solid #ddd;border-radius:8px;padding:7px 12px;font-size:13px;font-family:inherit;outline:none;transition:border .2s;width:200px}
        .search-input:focus{border-color:#3FA9F5}

        /* Trend badge */
        .trend-badge{display:inline-flex;align-items:center;gap:3px;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:700}
        .trend-down{background:#d4edda;color:#28a745}
        .trend-up{background:#fde8e8;color:#e74c3c}

        /* Section title */
        .section-title{font-size:15px;font-weight:700;color:#1F3C88;margin-bottom:2px}
        .section-sub{font-size:12px;color:#888;margin-bottom:0}

        /* Footer */
        .footer{border-top:1px solid #e8eaed;padding:20px 0;margin-top:32px;text-align:center;color:#888;font-size:12px}

        /* Animations */
        @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        .animate{animation:fadeUp .5s ease forwards}
        .d1{animation-delay:.05s;opacity:0}.d2{animation-delay:.1s;opacity:0}.d3{animation-delay:.15s;opacity:0}.d4{animation-delay:.2s;opacity:0}

        /* Loading */
        .loader-overlay{position:fixed;inset:0;z-index:999;background:rgba(255,255,255,.7);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center}
        .spinner{width:40px;height:40px;border:3px solid #E3F2FD;border-top-color:#1F3C88;border-radius:50%;animation:spin .7s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* Responsive */
        @media(max-width:1024px){.grid-4{grid-template-columns:repeat(2,1fr)}.grid-3-1{grid-template-columns:1fr}}
        @media(max-width:640px){.grid-4{grid-template-columns:1fr}.nav-flex{flex-direction:column;gap:12px}}
    </style>
</head>
<body x-data="dashboard()">

<!-- Loading -->
<div x-show="isLoading" x-cloak class="loader-overlay" x-transition><div class="spinner"></div></div>

<!-- Header -->
<header class="header-bg">
    <div class="nav-inner">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="nav-flex">
            <div style="display:flex;align-items:center;gap:12px">
                <img src="{{ asset('LOGOBPS.svg') }}" alt="Logo BPS" style="height:40px;width:auto;filter:brightness(0) invert(1)">
                <div>
                    <h1 style="font-size:18px;font-weight:800;color:#fff;line-height:1.2">TPT-BPS Dashboard</h1>
                    <p style="font-size:11px;color:rgba(255,255,255,.7)">Tingkat Pengangguran Terbuka · Data BPS WebAPI</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <select x-model="filters.periode" @change="fetchData()" class="filter-select">
                    <option value="Agustus">Agustus</option>
                    <option value="Februari">Februari</option>
                    <option value="all">Semua Periode</option>
                </select>
                <select x-model="filters.tahun" @change="fetchData()" class="filter-select">
                    <option value="all">Semua Tahun</option>
                    @foreach($tahuns as $t)<option value="{{ $t->tahun }}">{{ $t->tahun }}</option>@endforeach
                </select>
                <select x-model="filters.provinsi" @change="fetchData()" class="filter-select" style="max-width:180px">
                    <option value="all">Semua Provinsi</option>
                    @foreach($provinsis as $p)<option value="{{ $p->kode_wilayah }}">{{ ucwords(strtolower($p->nama_wilayah)) }}</option>@endforeach
                </select>
                <button @click="exportCSV()" class="btn-export">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export CSV
                </button>
            </div>
        </div>
    </div>
</header>

<main class="container" style="padding-top:24px;padding-bottom:24px">
    <!-- Summary Cards -->
    <div class="grid-4" style="margin-bottom:20px">
        <div class="card stat-card blue animate d1" style="padding:20px">
            <div style="display:flex;justify-content:space-between;align-items:start">
                <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#888">Rata-rata TPT</p>
                <template x-if="summary.trend !== null && summary.trend !== undefined">
                    <span class="trend-badge" :class="summary.trend < 0 ? 'trend-down' : 'trend-up'">
                        <span x-text="(summary.trend > 0 ? '▲ +' : '▼ ') + summary.trend + '%'"></span>
                    </span>
                </template>
            </div>
            <p style="font-size:32px;font-weight:800;color:#1F3C88;margin-top:6px"><span x-text="summary.avg_tpt">-</span><span style="font-size:18px">%</span></p>
            <p style="font-size:11px;color:#aaa;margin-top:4px">Tahun <span x-text="summary.current_year">-</span></p>
        </div>
        <div class="card stat-card red animate d2" style="padding:20px">
            <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#888">TPT Tertinggi</p>
            <p style="font-size:32px;font-weight:800;color:#e74c3c;margin-top:6px"><span x-text="summary.max_tpt">-</span><span style="font-size:18px">%</span></p>
            <p style="font-size:11px;color:#aaa;margin-top:4px" x-text="summary.max_region">-</p>
        </div>
        <div class="card stat-card green animate d3" style="padding:20px">
            <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#888">TPT Terendah</p>
            <p style="font-size:32px;font-weight:800;color:#4CAF50;margin-top:6px"><span x-text="summary.min_tpt">-</span><span style="font-size:18px">%</span></p>
            <p style="font-size:11px;color:#aaa;margin-top:4px" x-text="summary.min_region">-</p>
        </div>
        <div class="card stat-card cyan animate d4" style="padding:20px">
            <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#888">Jumlah Provinsi</p>
            <p style="font-size:32px;font-weight:800;color:#3FA9F5;margin-top:6px" x-text="summary.total_provinsi">-</p>
            <p style="font-size:11px;color:#aaa;margin-top:4px">Provinsi tercakup data</p>
        </div>
    </div>

    <!-- Charts Row: Trend + Doughnut -->
    <div class="grid-3-1" style="margin-bottom:20px">
        <div class="card" style="padding:20px">
            <p class="section-title">Tren TPT Nasional</p>
            <p class="section-sub">Pergerakan rata-rata TPT per tahun</p>
            <div style="position:relative;height:280px;margin-top:12px"><canvas id="lineChart"></canvas></div>
        </div>
        <div class="card" style="padding:20px">
            <p class="section-title">Distribusi Kawasan</p>
            <p class="section-sub">Rata-rata TPT per kawasan</p>
            <div style="position:relative;height:280px;margin-top:12px;display:flex;align-items:center;justify-content:center"><canvas id="doughnutChart"></canvas></div>
        </div>
    </div>

    <!-- Bar Chart -->
    <div class="card" style="padding:20px;margin-bottom:20px">
        <p class="section-title">Top 15 Provinsi — TPT Tertinggi</p>
        <p class="section-sub">Perbandingan antar provinsi pada tahun terpilih</p>
        <div style="position:relative;height:400px;margin-top:12px"><canvas id="barChart"></canvas></div>
    </div>

    <!-- Data Table -->
    <div class="card" style="padding:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
            <div>
                <p class="section-title">Detail Data</p>
                <p class="section-sub">Seluruh data TPT berdasarkan filter aktif · <span x-text="filteredTableData.length"></span> baris</p>
            </div>
            <input type="text" x-model="tableSearch" placeholder="🔍 Cari provinsi..." class="search-input">
        </div>
        <div style="overflow-x:auto;max-height:480px;overflow-y:auto">
            <table class="data-table">
                <thead style="position:sticky;top:0;z-index:2">
                    <tr>
                        <th>#</th><th>Provinsi</th><th>Tahun</th><th>Periode</th><th>TPT (%)</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, idx) in filteredTableData" :key="idx">
                        <tr>
                            <td style="color:#aaa" x-text="idx+1"></td>
                            <td style="font-weight:500" x-text="row.provinsi"></td>
                            <td x-text="row.tahun"></td>
                            <td x-text="row.periode"></td>
                            <td style="font-weight:700" :style="'color:' + (row.tpt >= 6 ? '#e74c3c' : row.tpt >= 4 ? '#d68910' : '#28a745')" x-text="row.tpt.toFixed(2)"></td>
                            <td><span class="badge" :class="row.tpt >= 6 ? 'badge-high' : row.tpt >= 4 ? 'badge-mid' : 'badge-low'" x-text="row.tpt >= 6 ? 'Tinggi' : row.tpt >= 4 ? 'Sedang' : 'Rendah'"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="filteredTableData.length===0" style="text-align:center;padding:32px;color:#aaa;font-size:14px">Tidak ada data ditemukan</div>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="container">
        <p>Data bersumber dari <strong>BPS WebAPI</strong> — Survei Angkatan Kerja Nasional (Sakernas)</p>
        <p style="margin-top:4px">Skripsi — Dashboard Visualisasi Data Statistik Publik</p>
    </div>
</footer>

<script>
// Store Chart.js instances outside Alpine to avoid reactive proxy wrapping
window._charts = {};

function dashboard(){
    return {
        filters:{tahun:'all',provinsi:'all',periode:'Agustus'},
        summary:{avg_tpt:'-',max_tpt:'-',max_region:'-',min_tpt:'-',min_region:'-',total_provinsi:0,current_year:'-',trend:null},
        tableData:[],tableSearch:'',rawData:[],isLoading:false,
        _chartsReady:false,

        get filteredTableData(){
            if(!this.tableSearch) return this.tableData;
            const s=this.tableSearch.toLowerCase();
            return this.tableData.filter(r=>r.provinsi.toLowerCase().includes(s));
        },

        init(){
            this.$nextTick(()=>{
                this.initCharts();
                this.fetchData();
            });
        },

        async fetchData(){
            this.isLoading=true;
            try{
                const p=new URLSearchParams(this.filters);
                const res=await fetch('{{ url('/api/dashboard/data') }}?'+p);
                if(!res.ok) throw new Error('HTTP '+res.status);
                const d=await res.json();
                this.summary=d.summary;
                this.tableData=d.tableData||[];
                this.rawData=d.raw_data||[];
                if(window._charts.line){window._charts.line.data=d.lineChart;window._charts.line.update('none')}
                if(window._charts.bar){window._charts.bar.data=d.barChart;window._charts.bar.update('none')}
                if(window._charts.doughnut){window._charts.doughnut.data=d.doughnutChart;window._charts.doughnut.update('none')}
            }catch(e){console.error(e)}
            finally{this.isLoading=false}
        },

        initCharts(){
            if(window._charts.line) return; // prevent double init
            Chart.defaults.font.family="'Inter',sans-serif";
            Chart.defaults.color='#666';
            const tt={backgroundColor:'#1F3C88',titleColor:'#fff',bodyColor:'#E3F2FD',borderColor:'#3FA9F5',borderWidth:1,padding:10,cornerRadius:8,titleFont:{weight:'bold'}};

            window._charts.line=new Chart(document.getElementById('lineChart'),{
                type:'line',data:{labels:[],datasets:[]},
                options:{responsive:true,maintainAspectRatio:false,interaction:{intersect:false,mode:'index'},
                    plugins:{legend:{display:false},tooltip:{...tt,callbacks:{label:c=>c.parsed.y.toFixed(2)+'%'}}},
                    scales:{y:{beginAtZero:false,grid:{color:'#f0f0f0'},ticks:{callback:v=>v+'%',font:{size:11}}},x:{grid:{display:false},ticks:{font:{size:11}}}}
                }
            });
            window._charts.bar=new Chart(document.getElementById('barChart'),{
                type:'bar',data:{labels:[],datasets:[]},
                options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',
                    plugins:{legend:{display:false},tooltip:{...tt,callbacks:{label:c=>c.parsed.x.toFixed(2)+'%'}}},
                    scales:{x:{beginAtZero:true,grid:{color:'#f0f0f0'},ticks:{callback:v=>v+'%',font:{size:11}}},y:{grid:{display:false},ticks:{font:{size:11}}}}
                }
            });
            window._charts.doughnut=new Chart(document.getElementById('doughnutChart'),{
                type:'doughnut',data:{labels:[],datasets:[]},
                options:{responsive:true,maintainAspectRatio:false,cutout:'60%',
                    plugins:{legend:{position:'bottom',labels:{padding:10,usePointStyle:true,pointStyle:'circle',font:{size:11}}},
                        tooltip:{...tt,callbacks:{label:c=>c.label+': '+c.parsed.toFixed(2)+'%'}}}
                }
            });
        },

        exportCSV(){
            if(!this.rawData.length) return;
            let csv="data:text/csv;charset=utf-8,Tahun,Periode,Kode,Provinsi,TPT\n";
            this.rawData.forEach(r=>{csv+=r.tahun+','+r.periode+','+r.kode_wilayah+',"'+r.nama_wilayah+'",'+r.tpt_value+'\n'});
            const a=document.createElement('a');a.href=encodeURI(csv);
            a.download='tpt_export_'+Date.now()+'.csv';
            document.body.appendChild(a);a.click();a.remove();
        }
    }
}
</script>
</body>
</html>
