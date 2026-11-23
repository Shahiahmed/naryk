@extends('site.layout')
@section('title', 'Ұрпақ капиталы — калькулятор')
@section('description', 'Бүгінгі инвестиция — ертеңгі ұрпақтың капиталы. Балаңыз 21 жасқа толғанша капитал жинау калькуляторы.')
@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<style>
.uk-page{padding:20px 0 36px;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#111827;-webkit-font-smoothing:antialiased}
.uk-page *,.uk-page *::before,.uk-page *::after{box-sizing:border-box}
.uk-shell{max-width:1120px;margin:0 auto;background:#f7faf8;border-radius:28px;overflow:hidden;box-shadow:0 20px 50px rgba(15,45,29,.12);border:1px solid rgba(20,108,62,.08)}
.uk-hero{position:relative;min-height:215px;padding:28px 36px 56px;display:flex;flex-direction:column;justify-content:space-between;background:linear-gradient(120deg,#0d3b24 0%,#1c5a38 55%,#8c7034 100%);overflow:hidden}
.uk-hero-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 40%;z-index:0}
.uk-hero::after{content:'';position:absolute;inset:0;background:linear-gradient(95deg,rgba(10,40,24,.88) 0%,rgba(16,56,34,.62) 48%,rgba(28,62,34,.28) 100%);z-index:1}
.uk-hero-content{position:relative;z-index:2;max-width:580px}
.uk-brand{display:inline-flex;align-items:center;gap:9px;margin-bottom:18px}
.uk-brand-mark{width:28px;height:28px;border-radius:7px;background:#22c55e;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(34,197,94,.35)}
.uk-brand-name{color:#fff;font-weight:800;font-size:16px;letter-spacing:.14em;text-transform:uppercase}
.uk-title{margin:0 0 8px;font-size:clamp(30px,4vw,44px);line-height:1.1;letter-spacing:-.02em}
.uk-title-gold{font-family:'Playfair Display',Georgia,serif;font-weight:700;color:#e7c675}
.uk-title-white{font-weight:800;color:#fff}
.uk-subtitle{margin:0;color:rgba(255,255,255,.9);font-size:15px;line-height:1.45}
.uk-main{position:relative;z-index:3;margin-top:-32px;padding:0 28px 28px;display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr);gap:22px;align-items:stretch}
.uk-card{background:#fff;border-radius:20px;padding:24px 26px;box-shadow:0 12px 32px rgba(15,52,33,.08);border:1px solid rgba(17,24,39,.05);display:flex;flex-direction:column}
.uk-step{padding-bottom:18px;margin-bottom:18px;border-bottom:1px solid #f0f2f1}
.uk-step:last-of-type{border-bottom:none;margin-bottom:16px;padding-bottom:0}
.uk-step-head{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.uk-step-num{width:26px;height:26px;border-radius:999px;background:#146c3e;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
.uk-step-title{margin:0;font-size:15px;font-weight:700;color:#111827;line-height:1.3}
.uk-step-sub{margin:-6px 0 12px 36px;font-size:12.5px;color:#6b7280}
.uk-slider-top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px}
.uk-label{font-size:13px;font-weight:500;color:#374151}
.uk-age-badge{display:inline-flex;align-items:center;padding:4px 12px;border-radius:999px;background:#e8f5ee;color:#0f5230;font-size:13.5px;font-weight:700}
.uk-range{-webkit-appearance:none;appearance:none;width:100%;height:8px;border-radius:999px;background:linear-gradient(to right,#146c3e 0%,#146c3e 52.38%,#dce5df 52.38%,#dce5df 100%);outline:none;cursor:pointer}
.uk-range::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:22px;height:22px;border-radius:50%;background:#fff;border:4px solid #146c3e;box-shadow:0 2px 8px rgba(20,108,62,.3)}
.uk-range::-moz-range-thumb{width:22px;height:22px;border-radius:50%;background:#fff;border:4px solid #146c3e;box-shadow:0 2px 8px rgba(20,108,62,.3);cursor:pointer}
.uk-slider-scale{display:flex;justify-content:space-between;margin-top:6px;font-size:12px;color:#6b7280;font-weight:500}
.uk-companies{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.uk-company-card{position:relative;display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:14px;border:1.5px solid #e5e7eb;background:#fff;cursor:pointer;transition:border-color .15s,background-color .15s;user-select:none;text-align:left;width:100%;font-family:inherit}
.uk-company-card.is-selected{border-color:#146c3e;background:#f3faf6;box-shadow:0 2px 10px rgba(20,108,62,.08)}
.uk-company-logo{width:34px;height:34px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0}
.uk-company-info{min-width:0;flex:1}
.uk-company-name{display:block;font-size:12.5px;font-weight:600;color:#111827;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.uk-company-meta{display:flex;align-items:center;gap:6px;margin-top:2px;font-size:11.5px;color:#6b7280}
.uk-company-rate{color:#146c3e;font-weight:700}
.uk-check{width:20px;height:20px;border-radius:999px;border:1.5px solid #cbd5e1;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;background:#fff}
.uk-company-card.is-selected .uk-check{background:#146c3e;border-color:#146c3e}
.uk-check svg{width:12px;height:12px;stroke:#fff;stroke-width:2.8;opacity:0}
.uk-company-card.is-selected .uk-check svg{opacity:1}
.uk-error{display:none;margin-top:8px;font-size:12.5px;font-weight:600;color:#dc2626}
.uk-error.is-visible{display:block}
.uk-inputs-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.uk-field{display:flex;flex-direction:column;gap:6px}
.uk-input-wrap{position:relative;display:flex;align-items:center}
.uk-input{width:100%;padding:11px 34px 11px 14px;border:1.5px solid #e5e7eb;border-radius:12px;font-family:inherit;font-size:15px;font-weight:600;color:#111827;background:#fff;outline:none}
.uk-input:focus{border-color:#146c3e;box-shadow:0 0 0 3px rgba(20,108,62,.12)}
.uk-currency{position:absolute;right:12px;font-size:15px;font-weight:600;color:#6b7280;pointer-events:none}
.uk-btn{margin-top:auto;width:100%;padding:14px 20px;border:none;border-radius:14px;background:#146c3e;color:#fff;font-family:inherit;font-size:15.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 8px 20px rgba(20,108,62,.24)}
.uk-btn:hover:not(:disabled){background:#0f5230}
.uk-btn:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}
.uk-results-card{background:linear-gradient(180deg,#f3faf6 0%,#fff 42%)}
.uk-results-head{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.uk-results-icon{width:36px;height:36px;border-radius:11px;background:#e8f5ee;color:#146c3e;display:inline-flex;align-items:center;justify-content:center}
.uk-results-title{margin:0;font-size:16px;font-weight:700;color:#111827}
.uk-total-value{font-size:clamp(32px,3.6vw,42px);font-weight:800;color:#146c3e;letter-spacing:-.025em;line-height:1.1;margin-bottom:6px;font-variant-numeric:tabular-nums}
.uk-total-caption{font-size:13px;color:#6b7280;margin-bottom:18px}
.uk-yield-box{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 16px;border-radius:14px;background:#e8f5ee;border:1px solid rgba(20,108,62,.14);margin-bottom:16px}
.uk-yield-left{display:flex;align-items:center;gap:9px;font-size:13.5px;font-weight:600;color:#0f5230}
.uk-yield-rate{font-size:20px;font-weight:800;color:#146c3e;font-variant-numeric:tabular-nums}
.uk-ratio-bar{display:flex;height:10px;border-radius:999px;overflow:hidden;background:#e5e7eb;margin-bottom:16px}
.uk-ratio-invested{background:#146c3e;transition:width .45s ease}
.uk-ratio-profit{background:#4ade80;transition:width .45s ease}
.uk-breakdown{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:18px}
.uk-stat-card{padding:14px;border-radius:14px;background:#f9fbfa;border:1px solid #e6ece8}
.uk-stat-label{display:flex;align-items:flex-start;gap:7px;font-size:12px;color:#6b7280;line-height:1.35;margin-bottom:8px;min-height:32px}
.uk-dot{width:9px;height:9px;border-radius:50%;margin-top:3px;flex-shrink:0}
.uk-dot--dark{background:#146c3e}
.uk-dot--light{background:#4ade80}
.uk-stat-amount{font-size:17px;font-weight:800;color:#111827;font-variant-numeric:tabular-nums;margin-bottom:4px}
.uk-stat-share{display:inline-block;font-size:12px;font-weight:700;color:#146c3e;background:#e8f5ee;padding:2px 8px;border-radius:999px}
.uk-disclaimer{margin-top:auto;display:flex;align-items:flex-start;gap:9px;padding:12px 14px;border-radius:12px;background:#f3f4f6;color:#6b7280;font-size:11.5px;line-height:1.45}
.uk-disclaimer-icon{width:17px;height:17px;border-radius:50%;border:1.5px solid #9ca3af;color:#6b7280;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
@media(max-width:900px){.uk-main{grid-template-columns:1fr;padding:0 18px 20px}.uk-hero{padding:24px 22px 48px;min-height:190px}}
@media(max-width:560px){.uk-card{padding:18px 16px}.uk-companies,.uk-inputs-grid,.uk-breakdown{grid-template-columns:1fr}}
</style>
@endpush
@section('content')
<div class="uk-page">
  <div class="uk-shell" id="urpaq-calculator">
    <header class="uk-hero">
      @if (file_exists(public_path('img/urpaq-kapitaly-hero.jpg')))
        <img class="uk-hero-bg" src="{{ asset('img/urpaq-kapitaly-hero.jpg') }}" alt="Ұрпақ капиталы">
      @endif
      <div class="uk-hero-content">
        <div class="uk-brand" aria-label="FREEDOM"><span class="uk-brand-mark"><svg width="15" height="17" viewBox="0 0 15 17" fill="none"><path d="M2 2H13V5H5.5V7.5H11.5V10.5H5.5V15H2V2Z" fill="#fff"/></svg></span><span class="uk-brand-name">FREEDOM</span></div>
        <h1 class="uk-title"><span class="uk-title-gold">Ұрпақ</span> <span class="uk-title-white">капиталы</span></h1>
        <p class="uk-subtitle">Бүгінгі инвестиция — ертеңгі ұрпақтың капиталы.</p>
      </div>
    </header>
    <div class="uk-main">
      <section class="uk-card" aria-label="Калькулятор параметрлері">
        <div class="uk-step">
          <div class="uk-step-head"><span class="uk-step-num">1</span><h2 class="uk-step-title">Мақсат — балаңыз 21 жасқа толғанша капитал жинау</h2></div>
          <div class="uk-slider-top"><label class="uk-label" for="uk-age-slider">Балаңыздың қазіргі жасы</label><span class="uk-age-badge" id="uk-age-display">11 жас</span></div>
          <input type="range" id="uk-age-slider" class="uk-range" min="0" max="21" step="1" value="11">
          <div class="uk-slider-scale"><span>0 жас</span><span>21 жас</span></div>
        </div>
        <div class="uk-step">
          <div class="uk-step-head"><span class="uk-step-num">2</span><h2 class="uk-step-title">Инвестициялық портфель</h2></div>
          <p class="uk-step-sub">Бірнешеуін таңдауға болады</p>
          <div class="uk-companies" id="uk-companies-grid" role="group"></div>
          <div class="uk-error" id="uk-portfolio-error" role="alert">Кемінде бір компанияны таңдаңыз</div>
        </div>
        <div class="uk-step">
          <div class="uk-step-head"><span class="uk-step-num">3</span><h2 class="uk-step-title">Инвестиция шарттары</h2></div>
          <div class="uk-inputs-grid">
            <div class="uk-field"><label class="uk-label" for="uk-initial-input">Бастапқы инвестиция</label><div class="uk-input-wrap"><input type="text" id="uk-initial-input" class="uk-input" inputmode="numeric" value="2 000 000" autocomplete="off"><span class="uk-currency">₸</span></div></div>
            <div class="uk-field"><label class="uk-label" for="uk-monthly-input">Ай сайынғы толықтыру</label><div class="uk-input-wrap"><input type="text" id="uk-monthly-input" class="uk-input" inputmode="numeric" value="50 000" autocomplete="off"><span class="uk-currency">₸</span></div></div>
          </div>
        </div>
        <button type="button" id="uk-calculate-btn" class="uk-btn"><span>Есептеу</span><span>→</span></button>
      </section>
      <section class="uk-card uk-results-card" aria-label="Есептеу нәтижесі">
        <div class="uk-results-head"><span class="uk-results-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg></span><h2 class="uk-results-title">Болашақтағы капитал</h2></div>
        <div class="uk-total-value" id="uk-total-output">0 ₸</div>
        <div class="uk-total-caption">Балаңыз 21-ге толғандағы ықтимал капитал</div>
        <div class="uk-yield-box"><div class="uk-yield-left"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg><span>Болжамды табыстылық</span></div><span class="uk-yield-rate" id="uk-yield-output">6,17%</span></div>
        <div class="uk-ratio-bar"><div class="uk-ratio-invested" id="uk-bar-invested" style="width:67%"></div><div class="uk-ratio-profit" id="uk-bar-profit" style="width:33%"></div></div>
        <div class="uk-breakdown">
          <div class="uk-stat-card"><div class="uk-stat-label"><span class="uk-dot uk-dot--dark"></span><span>Өзіңіз салған қаражат</span></div><div class="uk-stat-amount" id="uk-invested-output">0 ₸</div><span class="uk-stat-share" id="uk-invested-share">0%</span></div>
          <div class="uk-stat-card"><div class="uk-stat-label"><span class="uk-dot uk-dot--light"></span><span>Инвестициялық табыс (болжамды)</span></div><div class="uk-stat-amount" id="uk-profit-output">0 ₸</div><span class="uk-stat-share" id="uk-profit-share">0%</span></div>
        </div>
        <div class="uk-disclaimer"><span class="uk-disclaimer-icon">i</span><span>Бұл есеп тарихи деректерге негізделген және болашақтағы нақты табыстылыққа кепілдік бермейді. Нарық жағдайына байланысты инвестицияның құны өзгеруі мүмкін.</span></div>
      </section>
    </div>
  </div>
</div>
<script>
(function(){
  const COMPANIES=@json($companies);
  const selected=new Set(COMPANIES.filter(c=>c.defaultSelected).map(c=>c.ticker));
  const ageSlider=document.getElementById('uk-age-slider'),ageDisplay=document.getElementById('uk-age-display'),grid=document.getElementById('uk-companies-grid'),errEl=document.getElementById('uk-portfolio-error'),initIn=document.getElementById('uk-initial-input'),monIn=document.getElementById('uk-monthly-input'),btn=document.getElementById('uk-calculate-btn'),totOut=document.getElementById('uk-total-output'),yldOut=document.getElementById('uk-yield-output'),invOut=document.getElementById('uk-invested-output'),invSh=document.getElementById('uk-invested-share'),prfOut=document.getElementById('uk-profit-output'),prfSh=document.getElementById('uk-profit-share'),barInv=document.getElementById('uk-bar-invested'),barPrf=document.getElementById('uk-bar-profit');
  let cur={total:0,invested:0,profit:0},af=null;
  const fmtT=v=>Math.round(Number.isFinite(v)?v:0).toString().replace(/\B(?=(\d{3})+(?!\d))/g,' ')+' ₸';
  const fmtN=r=>{const d=String(r).replace(/\D/g,'');if(!d)return'';const c=d.replace(/^0+(?=\d)/,'');return c==='0'?'':c.replace(/\B(?=(\d{3})+(?!\d))/g,' ');};
  const parseA=el=>{const d=String(el.value).replace(/\D/g,'');return d?parseInt(d,10):0;};
  const fmtP=r=>String(Number(r.toFixed(2))).replace('.',',')+'%';
  function updSlider(){const a=parseInt(ageSlider.value,10)||0,p=(a/21)*100;ageSlider.style.background='linear-gradient(to right,#146c3e 0%,#146c3e '+p+'%,#dce5df '+p+'%,#dce5df 100%)';ageDisplay.textContent=a+' жас';}
  function updSel(){grid.querySelectorAll('.uk-company-card').forEach(c=>{const s=selected.has(c.dataset.ticker);c.classList.toggle('is-selected',s);c.setAttribute('aria-pressed',s?'true':'false');});const ok=selected.size>0;errEl.classList.toggle('is-visible',!ok);btn.disabled=!ok;}
  function render(){grid.innerHTML='';COMPANIES.forEach(co=>{const b=document.createElement('button');b.type='button';b.className='uk-company-card'+(selected.has(co.ticker)?' is-selected':'');b.dataset.ticker=co.ticker;b.innerHTML='<span class="uk-company-logo" style="background:'+co.color+'">'+co.badge+'</span><span class="uk-company-info"><span class="uk-company-name">'+co.name+'</span><span class="uk-company-meta"><span>'+co.ticker+'</span><span>·</span><span class="uk-company-rate">'+fmtP(co.rate)+'</span></span></span><span class="uk-check"><svg viewBox="0 0 16 16" fill="none"><path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';b.addEventListener('click',()=>{selected.has(co.ticker)?selected.delete(co.ticker):selected.add(co.ticker);updSel();});grid.appendChild(b);});}
  function avgRate(){if(!selected.size)return 0;let s=0;COMPANIES.forEach(c=>{if(selected.has(c.ticker))s+=Number(c.rate);});return s/selected.size;}
  function anim(t){if(af)cancelAnimationFrame(af);const st={...cur},t0=performance.now();function step(n){const k=Math.min(1,(n-t0)/520),e=1-Math.pow(1-k,3);totOut.textContent=fmtT(st.total+(t.total-st.total)*e);invOut.textContent=fmtT(st.invested+(t.invested-st.invested)*e);prfOut.textContent=fmtT(st.profit+(t.profit-st.profit)*e);if(k<1)af=requestAnimationFrame(step);else{cur={...t};totOut.textContent=fmtT(t.total);invOut.textContent=fmtT(t.invested);prfOut.textContent=fmtT(t.profit);af=null;}}af=requestAnimationFrame(step);}
  function calc(){if(!selected.size)return;const a=parseInt(ageSlider.value,10)||0,m=Math.max(0,(21-a)*12),P=parseA(initIn),PMT=parseA(monIn),r=avgRate(),i=(r/100)/12;let fv=P;if(m>0){fv=i>0?P*Math.pow(1+i,m)+PMT*((Math.pow(1+i,m)-1)/i):P+PMT*m;}const inv=P+PMT*m,prf=Math.max(0,fv-inv),ip=fv>0?Math.round((inv/fv)*100):100,pp=Math.max(0,100-ip);yldOut.textContent=fmtP(r);invSh.textContent=ip+'%';prfSh.textContent=pp+'%';barInv.style.width=ip+'%';barPrf.style.width=pp+'%';anim({total:fv,invested:inv,profit:prf});}
  ageSlider.addEventListener('input',updSlider);
  [initIn,monIn].forEach(el=>el.addEventListener('input',()=>{el.value=fmtN(el.value);}));
  btn.addEventListener('click',calc);
  render();updSel();updSlider();calc();
})();
</script>
@endsection
