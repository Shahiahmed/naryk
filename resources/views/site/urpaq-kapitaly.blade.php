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
.uk-hero::after{content:'';position:absolute;inset:0;background:linear-gradient(95deg,rgba(7,51,34,.85) 0%,rgba(7,51,34,.35) 48%,rgba(7,51,34,0) 72%);z-index:1}
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
      <img class="uk-hero-bg" src="data:image/webp;base64,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" alt="Ұрпақ капиталы">
      <div class="uk-hero-content">
        <div class="uk-brand" aria-label="FREEDOM"><img src="data:image/png;base64,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" alt="FREEDOM" style="height:28px;width:auto;display:block;"><span class="uk-brand-name">FREEDOM</span></div>
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
