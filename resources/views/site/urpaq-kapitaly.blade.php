@extends('site.layout')
@section('title', 'Ұрпақ капиталы — калькулятор')
@section('description', 'Бүгінгі инвестиция — ертеңгі ұрпақтың капиталы. Балаңыз 21 жасқа толғанша капитал жинау калькуляторы.')
@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
.uk-page{padding:16px 0 32px;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#111827;-webkit-font-smoothing:antialiased}
.uk-page *,.uk-page *::before,.uk-page *::after{box-sizing:border-box}
.uk-shell{max-width:1180px;margin:0 auto;background:#f4f7f5;border-radius:22px;overflow:hidden;box-shadow:0 14px 40px rgba(15,45,29,.10);border:1px solid rgba(20,83,45,.08)}
.uk-hero{position:relative;width:100%;aspect-ratio:1179/254;min-height:170px;background:#073322;overflow:hidden}
.uk-hero-bg{width:100%;height:100%;object-fit:cover;object-position:left top;display:block}
.uk-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.uk-main{position:relative;z-index:3;margin-top:-24px;padding:0 18px 20px;display:grid;grid-template-columns:minmax(0,44.5fr) minmax(0,55.5fr);gap:16px;align-items:stretch}
.uk-card{background:#fff;border-radius:18px;padding:20px 22px;box-shadow:0 6px 22px rgba(15,52,33,.06);border:1px solid rgba(17,24,39,.05);display:flex;flex-direction:column}
.uk-step{margin-bottom:16px}
.uk-step:last-of-type{margin-bottom:16px}
.uk-step-head{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.uk-step-num{width:26px;height:26px;border-radius:999px;background:#16573b;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
.uk-step-title{margin:0;font-size:15px;font-weight:700;color:#111827;line-height:1.3}
.uk-step-sub{margin:-4px 0 10px 36px;font-size:12.5px;color:#6b7280}
.uk-label{display:block;font-size:13px;font-weight:500;color:#6b7280;margin-bottom:6px}
.uk-slider-scale-top{position:relative;display:flex;justify-content:space-between;align-items:center;height:22px;margin-bottom:6px;font-size:12.5px;color:#6b7280;font-weight:500}
.uk-age-floating{position:absolute;top:0;transform:translateX(-50%);font-size:14px;font-weight:800;color:#16573b;white-space:nowrap;pointer-events:none}
.uk-range{-webkit-appearance:none;appearance:none;width:100%;height:7px;border-radius:999px;background:linear-gradient(to right,#16573b 0%,#16573b 52.38%,#e2e8f0 52.38%,#e2e8f0 100%);outline:none;cursor:pointer}
.uk-range::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:22px;height:22px;border-radius:50%;background:#fff;border:5px solid #16573b;box-shadow:0 2px 6px rgba(22,87,59,.28)}
.uk-range::-moz-range-thumb{width:22px;height:22px;border-radius:50%;background:#fff;border:5px solid #16573b;box-shadow:0 2px 6px rgba(22,87,59,.28);cursor:pointer}
.uk-companies{display:flex;flex-direction:column;gap:8px}
.uk-company-row{display:flex;align-items:center;gap:12px;width:100%;padding:7px 14px 7px 9px;border-radius:12px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;transition:border-color .15s,background-color .15s;text-align:left;font-family:inherit}
.uk-company-row:hover{border-color:#9bc9ae}
.uk-company-row.is-selected{border-color:#84bfa0;background:#f0f8f3}
.uk-company-logo-box{width:34px;height:34px;border-radius:8px;background:#fff;border:1px solid #edf2ee;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
.uk-company-logo-box img{width:28px;height:28px;object-fit:contain;display:block}
.uk-company-text{flex:1;min-width:0;font-size:14.5px;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.uk-company-name{font-weight:700;color:#111827}
.uk-company-ticker{font-weight:500;color:#6b7280;margin-left:3px}
.uk-checkbox{width:20px;height:20px;border-radius:6px;border:1.5px solid #cbd5e1;background:#fff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s}
.uk-company-row.is-selected .uk-checkbox{background:#16573b;border-color:#16573b}
.uk-checkbox svg{width:13px;height:13px;stroke:#fff;stroke-width:2.8;opacity:0}
.uk-company-row.is-selected .uk-checkbox svg{opacity:1}
.uk-error{display:none;margin-top:8px;font-size:12.5px;font-weight:600;color:#dc2626}
.uk-error.is-visible{display:block}
.uk-inputs-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.uk-field{display:flex;flex-direction:column}
.uk-input-wrap{position:relative;display:flex;align-items:center}
.uk-input{width:100%;padding:11px 32px 11px 14px;border:1.5px solid #e2e8f0;border-radius:12px;font-family:inherit;font-size:15px;font-weight:700;color:#111827;background:#fff;outline:none}
.uk-input:focus{border-color:#16573b;box-shadow:0 0 0 3px rgba(22,87,59,.12)}
.uk-currency{position:absolute;right:12px;font-size:15px;font-weight:600;color:#9ca3af;pointer-events:none}
.uk-btn{margin-top:auto;width:100%;padding:14px 20px;border:none;border-radius:12px;background:#16573b;color:#fff;font-family:inherit;font-size:15.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:background-color .15s,opacity .15s}
.uk-btn:hover:not(:disabled){background:#10432d}
.uk-btn:disabled{opacity:.45;cursor:not-allowed}
.uk-right-shell{background:#fff;border-radius:18px;padding:14px;box-shadow:0 6px 22px rgba(15,52,33,.06);border:1px solid rgba(17,24,39,.05);display:flex;flex-direction:column;gap:12px}
.uk-results-panel{flex:1;background:#f1f8f4;border-radius:14px;padding:24px 24px 22px;display:flex;flex-direction:column;justify-content:space-between}
.uk-results-head{display:flex;align-items:center;gap:12px}
.uk-results-icon{width:48px;height:48px;border-radius:999px;background:#e0efe6;color:#16573b;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
.uk-results-title{margin:0;font-family:Georgia,'Times New Roman',serif;font-size:21px;font-weight:700;color:#111827}
.uk-total-block{margin:24px 0 20px}
.uk-total-value{font-size:clamp(36px,4.2vw,50px);font-weight:800;color:#16573b;letter-spacing:-.025em;line-height:1.08;margin-bottom:8px;font-variant-numeric:tabular-nums;white-space:nowrap}
.uk-total-caption{font-size:15px;color:#374151}
.uk-yield-box{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-radius:14px;background:#e1efe7;margin-bottom:20px}
.uk-yield-left{display:flex;align-items:center;gap:10px;font-size:15.5px;font-weight:600;color:#1f2937}
.uk-yield-left svg{color:#16573b;flex-shrink:0}
.uk-yield-rate{font-size:26px;font-weight:800;color:#16573b;font-variant-numeric:tabular-nums;white-space:nowrap}
.uk-breakdown-card{background:#fff;border-radius:14px;border:1px solid #e4eee8;display:grid;grid-template-columns:1fr 1fr;overflow:hidden}
.uk-stat-half{padding:18px 18px 16px}
.uk-stat-half:first-child{border-right:1px solid #e6eee9}
.uk-stat-label{display:flex;align-items:flex-start;gap:8px;font-size:14px;color:#374151;line-height:1.35;margin-bottom:12px;min-height:38px}
.uk-dot{width:10px;height:10px;border-radius:50%;margin-top:4px;flex-shrink:0}
.uk-dot--dark{background:#16573b}
.uk-dot--light{background:#78b997}
.uk-stat-amount{font-size:21px;font-weight:800;color:#111827;font-variant-numeric:tabular-nums;white-space:nowrap;margin-bottom:4px}
.uk-stat-share{font-size:14px;font-weight:500;color:#6b7280}
.uk-disclaimer{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:12px;background:#f7faf8;border:1px solid #e8eeea;color:#6b7280;font-size:12px;line-height:1.45}
.uk-disclaimer-icon{width:18px;height:18px;border-radius:50%;border:1.5px solid #9ca3af;color:#6b7280;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
@media(max-width:920px){.uk-main{grid-template-columns:1fr;padding:0 14px 18px}.uk-hero{aspect-ratio:auto;height:165px}}
@media(max-width:560px){.uk-card,.uk-results-panel{padding:16px}.uk-inputs-grid,.uk-breakdown-card{grid-template-columns:1fr}.uk-stat-half:first-child{border-right:none;border-bottom:1px solid #e6eee9}.uk-total-value{font-size:32px}}
</style>
@endpush
@section('content')
<div class="uk-page">
  <div class="uk-shell" id="urpaq-calculator">
    <header class="uk-hero">
      <img class="uk-hero-bg" src="data:image/webp;base64,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" alt="FREEDOM — Ұрпақ капиталы. Бүгінгі инвестиция — ертеңгі ұрпақтың капиталы.">
      <h1 class="uk-sr-only">Ұрпақ капиталы — Бүгінгі инвестиция — ертеңгі ұрпақтың капиталы.</h1>
    </header>

    <div class="uk-main">
      <section class="uk-card" aria-label="Калькулятор параметрлері">
        <div class="uk-step">
          <div class="uk-step-head"><span class="uk-step-num">1</span><h2 class="uk-step-title">Мақсат — балаңыз 21 жасқа толғанша капитал жинау</h2></div>
          <label class="uk-label" for="uk-age-slider">Балаңыздың қазіргі жасы</label>
          <div class="uk-slider-scale-top">
            <span>0 жас</span>
            <span class="uk-age-floating" id="uk-age-display" style="left:52.38%">11 жас</span>
            <span>21 жас</span>
          </div>
          <input type="range" id="uk-age-slider" class="uk-range" min="0" max="21" step="1" value="11">
        </div>

        <div class="uk-step">
          <div class="uk-step-head"><span class="uk-step-num">2</span><h2 class="uk-step-title">Инвестициялық портфель</h2></div>
          <p class="uk-step-sub">Бірнешеуін таңдауға болады</p>
          <div class="uk-companies" id="uk-companies-grid" role="group" aria-label="Инвестициялық портфель"></div>
          <div class="uk-error" id="uk-portfolio-error" role="alert">Кемінде бір компанияны таңдаңыз</div>
        </div>

        <div class="uk-step">
          <div class="uk-step-head"><span class="uk-step-num">3</span><h2 class="uk-step-title">Инвестиция шарттары</h2></div>
          <div class="uk-inputs-grid">
            <div class="uk-field">
              <label class="uk-label" for="uk-initial-input">Бастапқы инвестиция</label>
              <div class="uk-input-wrap">
                <input type="text" id="uk-initial-input" class="uk-input" inputmode="numeric" value="2 000 000" autocomplete="off">
                <span class="uk-currency">₸</span>
              </div>
            </div>
            <div class="uk-field">
              <label class="uk-label" for="uk-monthly-input">Ай сайынғы толықтыру</label>
              <div class="uk-input-wrap">
                <input type="text" id="uk-monthly-input" class="uk-input" inputmode="numeric" value="50 000" autocomplete="off">
                <span class="uk-currency">₸</span>
              </div>
            </div>
          </div>
        </div>

        <button type="button" id="uk-calculate-btn" class="uk-btn"><span>Есептеу</span><span aria-hidden="true">→</span></button>
      </section>

      <div class="uk-right-shell">
        <section class="uk-results-panel" aria-label="Есептеу нәтижесі">
          <div class="uk-results-head">
            <span class="uk-results-icon" aria-hidden="true">
              <svg width="26" height="26" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="22" height="22" rx="5.5"/><path d="M8 20v-4.5M12 20v-6.5M16 20v-5M20 20v-8.5"/><path d="M7.5 11.5L11.5 8.2L15.5 10.5L20.5 7.2"/></svg>
            </span>
            <h2 class="uk-results-title">Болашақтағы капитал</h2>
          </div>

          <div class="uk-total-block">
            <div class="uk-total-value" id="uk-total-output">0 ₸</div>
            <div class="uk-total-caption">Балаңыз 21-ге толғандағы ықтимал капитал</div>
          </div>

          <div class="uk-yield-box">
            <div class="uk-yield-left">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 20v-4M9 20v-7M14 20v-9M19 20v-12"/>
                <path d="M4 12l5-4 4 3 6-6"/>
                <path d="M15 5h4v4"/>
              </svg>
              <span>Болжамды табыстылық</span>
            </div>
            <span class="uk-yield-rate" id="uk-yield-output">6,17%</span>
          </div>

          <div class="uk-breakdown-card">
            <div class="uk-stat-half">
              <div class="uk-stat-label"><span class="uk-dot uk-dot--dark"></span><span>Өзіңіз салған қаражат</span></div>
              <div class="uk-stat-amount" id="uk-invested-output">0 ₸</div>
              <div class="uk-stat-share" id="uk-invested-share">0%</div>
            </div>
            <div class="uk-stat-half">
              <div class="uk-stat-label"><span class="uk-dot uk-dot--light"></span><span>Инвестициялық табыс (болжамды)</span></div>
              <div class="uk-stat-amount" id="uk-profit-output">0 ₸</div>
              <div class="uk-stat-share" id="uk-profit-share">0%</div>
            </div>
          </div>
        </section>

        <div class="uk-disclaimer">
          <span class="uk-disclaimer-icon" aria-hidden="true">i</span>
          <span>Бұл есеп тарихи деректерге негізделген және болашақтағы нақты табыстылыққа кепілдік бермейді. Нарық жағдайына байланысты инвестицияның құны өзгеруі мүмкін.</span>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  const COMPANIES = [{"ticker": "KZTO", "name": "ҚазТрансОйл", "rate": 10.2, "logo": "data:image/webp;base64,UklGRlYDAABXRUJQVlA4IEoDAACQDwCdASowADAAPj0Yi0OiIaEVWq4AIAPEsYBOmbsoUSbvyeru+Pfvn5UZPz8Fw27wn+Tf3LJdPln+A7+n+k9GfpTrXkgfnufQHpX+oP+T6tf/KWHArb1S6zMEgsUvwxM/jLAEOHKpEw5LP3wpaIEXLsLgnzbjy6MurcVlYqHF1CzZJ6AAAP7//moQfXJ0nsGXey77AMbs4HWVz8qs8KWcvAv67IKpub0aRuiYT0ZNue3wQ7OrmIe51iGEOmfoI/70uo/oSlM9x8KGmX+cx7fmTQYseL5TSMPyIEzXBBp3Vyq0uVPFmAKdIB9SoUUrMm+vSFiVQeUf0mfvq8PkHufiWFFS7/0MPOj/+lOkaoizWingw9eBFfwpmSqQS3+4F+J2Uts/CP0NJ/TbCjG3I3ERWSIOo9+mdNfu2oaZeav75rb3+ZlM3YsGCvB/0tSpyDigWb0SDgQuBCIx7lN1Gm52M5IqiW7t1duKRfvh/+Asgm6B/PP/nd6fqdV+H8EJvaWbtH11VG7VBLBwsCNMG1I+g2zldefzZtnLLleMCaoOdj9AKvor2Bu4KBswKgrXY+fyDTYFL8uw/P+X8L23WH9oHabcxGBFMNjbA0J1fBVeOz7OZ0QXHO08wmP02voc1/xex6/jHIgSEEcys2iRT/J0b+wyMWf+KldO/zoUJJNcWE5NF7sYs2TXenL2sG4/+w5fzYbee3ahi7vWZjF3PzwKEfmrt9su4EPTApntR31xXn4uEpN/nsF4ptwYnZd0DTQ6zIhmSm0X4yP+efVirJIb1/h7psbwiAUdSOr7wGU6fdz1c7QGXHS/nnc4ucyW3zI2+a8CmU7zsMUdVDhYT6PtQuB+VDHte184rl2PhVF8d2aZ5gyeN6eF1pPqLzf3AIr/wE4qFdr7Xn+RhaEzEkHmwwBTf64X9btSZ+rtSZkTnRGlFdxdgH3WAEl+XnPqemqOR05qePD7nqgmWP/AVgX0LR7mhRTIwRVguVK8TM4NPEE/t6HZvINkvtloa6C6QxMJfcABKNwE+Fhux8iOaQU/Zl2/zOvmSJBnN1G17Bal1/8Wwyenz8V/KI70JZvFDu6tGB7LqhLTyHOGuK6y0Dz0xUlo5hWi0AAAAA==", "defaultSelected": true}, {"ticker": "KEGC", "name": "KEGOC", "rate": 15.4, "logo": "data:image/webp;base64,UklGRtYCAABXRUJQVlA4IMoCAAAQDwCdASowADAAPjkWiEMiISEarVVUIAOEsoBlSmAXgBuKtCy5W/Dh8H9tzOmSU/Lv8xoz3HsRveqL/Y/9n8ZvaR9N+wV+sH+hTL79n80zTLkcFFmcyXpFj0GZEi3Rpp/w5Afv57VDKiAAnTISQDcyG76NSvgqiDIpUuJ0UagWaAAA/v/+WqWk7a4wPAdKUmWx8eAXxOeHTtyCsbSw5uztZC1eQbqSGSMBPnMQlw11FjJPr/1D2tPb4AmLpSAU/OBBE8evvlZgT3DxTQwalZBq/zfyIlf0avTkF2RVOKkSxaebXEgdk2T6/WuoXKL5zOo55KbtAorVgVBKeZfYN4ZyMm0jzTnePsyokQvOtwdCFar/rHncGqUWxmuS9iU6ZHCGNuToniFrJQo/1zltVwbsx6h0fYe94P6rDhUorsGcK2q6Uh1so3n40nRTBEmoUYBrSklqP6dfSRjapXRgHdeOZJ4MjeuIu75rZBFZaDjR+c8p8I+F+Ct2+oI8NK/2lD4wqwkl/Ey9kCeDEpbM1e1lCiEDWDT8cp9AlkvmnOKwftbq45FC/3bqnlyoq9JsPwflNUeDWMa9lbRuaL+34yK33e6OrGGM/lKfzln7eIv1J4IHT+D+WSL+ws8ukwR2rbwk838QOspbL0p3jVv6EtYB0RXlYZKzn9SPMoDgjvZ9EZpoWSsjtDIoso88TULd3XzOTIB4a69TbN4ch4LpmK/xQU1p/9pSF0W7Zf+xP2zanPBFmtlZK/VfGEkcf6XHedmjZSfAyW/AbduTTbj0upgSiX8v9q7QrxRtrRd4UbXmeWGAyIAN0Jyvf4gVkr+4Gu/zu8XLEeYaUV/9K4lYfVVOvMz5sOnF0OtAWblQxgAdf3nfCnH9rt6HrzccbBPscVs3zX8VurGKqomiXVBpALKgrtnZBs5RNeWJ73bYldtlp6bHD5qsnjoAAAA=", "defaultSelected": false}, {"ticker": "KZTK", "name": "Қазақтелеком", "rate": 5.7, "logo": "data:image/webp;base64,UklGRiYDAABXRUJQVlA4IBoDAADwDgCdASowADAAPj0YiUMiIaEarAQAIAPEswBajUJDQkCVD+sa70SOExtpfMZ+0HrG9EB1IG9JT7Lln9QICbsP4eceJbBmm/2jU0+WoHGvJaTev54xi+FTvMU0yMuyMxO9fnmII9ivJSobv5k2AA/52O1PGPolzUT+Fcv5PaJ+AAD+//5yKn1QRXr/OpPsdNKq2T6bS05PYA4UtthL7umnpOzWESlHo1qq4hQEXn/vS4T3oaPH/ZeAasSZ8m/WOSKeZeTJ8Dx8UUMWwUxHLt5zCje3Csnb9mDzJ4OFqnlH4iePM/8VYNTnNs9TtjoGb7CsIpeAwxAiw3wU5dYhIjO1E6ARDjuo7gkEoBoTxTlzLfZli1TmfJ70D3pMxp0DVcEwHp/6bHAH9uZgNyTzW/tk26XbbNbEnqPrsFXRe0HYIX5TPCKmO3pMHPTkqvn1fGxMs2l5H703+c+vWwr/57TQty8Ou/bdzfl2liVzNxiPp2H+LGBtgDDd/7Kv2SELlSdJz4CDwax6wKs4CKiDspmZTeg2c0rjgWu7M0wUn3phMot2orT+xIzvjAgb+4XW+NYUAOze8tJPQf+eAoPlIppSj0/HimtzeT/I/sHbW/brFLm0Lf4aJoeZ8f7dZCIs2CBKGLl+0pm12wR+JCgisxxJj/v0LZr6FfdHZDhlmFd3SlmHzS6f61s6wFG4rQ/G1M2jSUzNzM3q6CVnXvaDbVo+PbG3F+LcUQ5TR4pKSFfHOqVkzoIH++eCnh4ZVXVj/ieXN/6v9adwpwOkT6MsYs89omtIRuksx/wR+jr1j4Tpuv1cj+KRvyZn/jvmVkkni+2mEXjFWVyfZ4+F5c1e3fYOEdwkiWCWuEgYYSzxCIb/ere1+DlaLRTryTjQE2HtTUemBt5oc//dCMkFh0HWt3eT2RKYaAuFREZX/IZUhKPNYL6dYqwHcxEwuUv9UrrG0D8eE6z11+vzKNVF6CRGPpYJ4zUEEAAo+XoeURuzWcWbnn4PPqfntrKCaRoamepmE0Gv0BDQ82PB6e5p1aCnTNfjvl2Px9vpgAAAAA==", "defaultSelected": true}, {"ticker": "CCBN", "name": "ЦентрКредит Банкі", "rate": 7.5, "logo": "data:image/webp;base64,UklGRngCAABXRUJQVlA4IGwCAABQDACdASowADAAPjkWiUMiISEaqq6oIAOEoAvRNoEL6WBjHq8C3bzK6yPoT/kEZriNQHidpsge41cfFW+OfD+mlsJE2dmalYRlaNG/ugx+jOdiOmuGjpkXEBHlTTqt86VCl+5N2AtPcevAAAD+//4nqkzhIapssWTX0gzU+tMNZZBbuPz0iTEH61gixLUt4psp4WIkjDUZs+535VH4rcabtzHkV7E5qBc9N3Vm98t+HAIxH5hBpWv+XOpWHi8fNFx1+n5NJH6p59121WKWbecNDM6O9D1DlaOOnOr89QAyAvup7gtbsLOwX9+y89rYUaP9hVPBnubLJG5dvJY/LXBktYvuNRv2dj7UEc1iuSulAXZPkFePbiLc0I+5ZBXjyBU5lcjQl85KrHAqc6hFh6FTqwkoD/Ennfy/v1i5PZ3gHKAwIPdnWxQQzEKLA/bpOs8XdDjmdXcmmRyl4vab9+rJojBzp8bD25Pv26wrdNcVPpPLkkw7QFXjSxypqG6gaG56m82OYjX8XQ/a7BERjLOuJ9LyYX5/Q87kVrNeyO26vfdeapXQREt8B/bUVek2bPnjcATPJaMoesyg3dqrNJcEs5lBSX1vvMa5lXC/hh0t+GiIEnywqlI+UvBX8CuBCRD0r7Zl/gftblfS5BKEEGfjo6o1Zb/q0iFBHfY9cwCSEQxHvGD1mMRk73mIbclCzynFK59SNt6tDsFvLc6dYPlZ6IZ5DzqCtB6ImvKTRw7sXO2x0U65Zs/xWW8RtRjT8C4aa5+IATNPygHMk+1E+4BHYMmxAkVa6l3oaJjkbKH7KEKNX+WnS8xQcAAAAA==", "defaultSelected": false}, {"ticker": "HSBK", "name": "Halyk Bank", "rate": 2.6, "logo": "data:image/webp;base64,UklGRpgCAABXRUJQVlA4IIwCAACQDgCdASowADAAPjkWiEMiISEarAaoIAOEswBgh2NIUzF+p8IaUHqt5AN5G6aXmA88foT+oR3mv9qv2Z9iuKl1AVtaEKJ0DGuaYxAejvZmmzvddr9DFXvuDyBqpCVb2eyy/9INwS0BSh8+9il/5ZvaNpfFdmD6LVoM26GQAAD+//4wFymsJCaRUclEWXbf2pk6bJp0f6P40lj0bJPzJ+L6vRTbGP/Or0Bc+dDjZ17oX0Z5y9CzPE9YD+A5lnQu92dFKH28CQj81cfmpTXWOsxytZhu9ln2hmfrcImC1jrLczf+YIer+lkOOQHDcjtQCpKO2XMkPowBn6d0rgEvnVvLTvaCT9AhWZR3gjFCSYQ6rw8AbP419MYCb6V9eO9EDjazz8ZEX+j2Fy8slNfe+uPGUnCB7M03QSd/tjxiElcVeLnIydS8F6ym+Ah+UBFSVd57c/gvBfp/FTdG4mSnXK48HD4PbQGB/8NH4CLc02WCQ55z8iQC/VR+mFJzGB/z/2urJ1byi80PLP5RkmVa0/1T61Fj6FoMsJ5N19TfzAFl3Tqm9IEYTO/CNVlr7WdecYwf1YfJh/snkYswXacd+YbgbdQInQPhotf4m9EVLo6rDaL0s3jqGpidmU4Hf2WPNNhT8u0/vJ3LkMAbwWPQlj60nAAvd21pLrYT8f0SPTjVXOCUVDGqrPJPX0CmztvJnuGPXR1I/0gReOPsqty4qoT0JUIkav2oBmMHTIWlsqcDhnnMmVqH/+U4cqxK3sQHvo193wq/kprtIfztbvkaBV7Ru8/bZF8iCSPH4asil/nMlmqzhAA9BV9uI9d/X/9Aypf7dm+0ohEVA3IsHfUA6QYs5+l1WwrPMjcudgAA", "defaultSelected": true}, {"ticker": "KCEL", "name": "Kcell", "rate": 7.7, "logo": "data:image/webp;base64,UklGRhgDAABXRUJQVlA4IAwDAADwDQCdASowADAAPj0aiUOiIaEXHATMIAPEoAwiWXo1aWStGHv34+ZRr9BnIv6/j1f8PbSpm3lR+efRg/26k2hfJu5DKr48F4vmqxyJsFNIuNM+n5rNbVnKwE/hyP8jXD+XeEt8UhOvfW8vIntH94vnU7Bjx04OXQAA/v/+lO2G6x4Fystma0bZuegRL13pC2vobWTiG2qQ9QSTj2bZL+Qg2E6AH92YOZjRGt0mfn1txzfFwLLYkIrZBfJp++uOzTl+t1J/4HwMX6p5R19v0Wne82D/cNIdX66HenTra14CgPFmZtmZvKSanNaHi4PwK+nCBKYef+bCUC48grZijpH3Zkx23qc7X/DMB+DQA58P7oGbgAM+KAMkUBl8nUs7LBwRu8EaqHz7oWNCP6g9l/FpDatTeK8Wxz9RS4i39e5ojZAZv9FvSQju5hc9Lwgr4Vkp2Wf//iv9+tStns10DPuaUYXoypsekcf8slnc6VxruugL2rq3/QHdmjYtyT7NqwPFeWjeX4+kdUYOJawqeYFtiGyqye33tRwGPpNHaIKYdbA/aN1ln4jaMDS1tPxzyhb2upmO+ZVzc8CT1zokL+Evyf6MXdkbBdvdknPOYxrit90mtk1dlUnOO2FqBlOX5JatMFkYTj1FSp58HYkLAQcN4vXEOAZv5ONSZ91+TDzip1ILmrL7Kt3Q3/Jsx6RBbwb4tzQl3yEq5vp1Scytj8MEsiODvKvkh/trengYn6Rekdz59d2dBpFySladg51gmYh3JwbFmf8xnD9jxrM//lZopyUyqWNArejAnazmh/mxppDMOgc/LpUzPwcHn0E6O/xqsIvPG8/lOZoxI8+Fk6nQ0u1mDEUVrj368Getyp2mOUT4sQx0K7nGWCnpjmBCdFPYZoIZvmTJi82/ceeAzzu6XC8dqCh929FC898NQH1CiyyhSK6BZa5Yo3GBV1qG7WrLtGKAOfGEom3y5Nvi6MpAvUcgoM9ZlZZ4pOwY1nOwjJYUF3B+5F4GpItYwJYAeXrmJjx+bEG+oH8AAAA=", "defaultSelected": false}];
  const selected = new Set(COMPANIES.filter(c => c.defaultSelected).map(c => c.ticker));
  const ageSlider = document.getElementById('uk-age-slider');
  const ageDisplay = document.getElementById('uk-age-display');
  const grid = document.getElementById('uk-companies-grid');
  const errEl = document.getElementById('uk-portfolio-error');
  const initIn = document.getElementById('uk-initial-input');
  const monIn = document.getElementById('uk-monthly-input');
  const btn = document.getElementById('uk-calculate-btn');
  const totOut = document.getElementById('uk-total-output');
  const yldOut = document.getElementById('uk-yield-output');
  const invOut = document.getElementById('uk-invested-output');
  const invSh = document.getElementById('uk-invested-share');
  const prfOut = document.getElementById('uk-profit-output');
  const prfSh = document.getElementById('uk-profit-share');

  let cur = { total: 0, invested: 0, profit: 0 };
  let af = null;

  const fmtT = v => Math.round(Number.isFinite(v) ? v : 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₸';
  const fmtN = r => {
    const d = String(r).replace(/\D/g, '');
    if (!d) return '';
    const c = d.replace(/^0+(?=\d)/, '');
    return c === '0' ? '' : c.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  };
  const parseA = el => {
    const d = String(el.value).replace(/\D/g, '');
    return d ? parseInt(d, 10) : 0;
  };
  const fmtP = r => String(Number(r.toFixed(2))).replace('.', ',') + '%';

  function updSlider() {
    const a = parseInt(ageSlider.value, 10) || 0;
    const pct = (a / 21) * 100;
    ageSlider.style.background = 'linear-gradient(to right,#16573b 0%,#16573b ' + pct + '%,#e2e8f0 ' + pct + '%,#e2e8f0 100%)';
    ageDisplay.textContent = a + ' жас';
    const clamped = Math.min(88, Math.max(12, pct));
    ageDisplay.style.left = clamped + '%';
  }

  function updSel() {
    grid.querySelectorAll('.uk-company-row').forEach(c => {
      const s = selected.has(c.dataset.ticker);
      c.classList.toggle('is-selected', s);
      c.setAttribute('aria-pressed', s ? 'true' : 'false');
    });
    const ok = selected.size > 0;
    errEl.classList.toggle('is-visible', !ok);
    btn.disabled = !ok;
  }

  function render() {
    grid.innerHTML = '';
    COMPANIES.forEach(co => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'uk-company-row' + (selected.has(co.ticker) ? ' is-selected' : '');
      b.dataset.ticker = co.ticker;
      b.innerHTML =
        '<span class="uk-company-logo-box"><img src="' + co.logo + '" alt="' + co.name + '"></span>' +
        '<span class="uk-company-text"><span class="uk-company-name">' + co.name + '</span> <span class="uk-company-ticker">(' + co.ticker + ')</span></span>' +
        '<span class="uk-checkbox" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
      b.addEventListener('click', () => {
        selected.has(co.ticker) ? selected.delete(co.ticker) : selected.add(co.ticker);
        updSel();
      });
      grid.appendChild(b);
    });
  }

  function avgRate() {
    if (!selected.size) return 0;
    let s = 0;
    COMPANIES.forEach(c => { if (selected.has(c.ticker)) s += Number(c.rate); });
    return s / selected.size;
  }

  function anim(t) {
    if (af) cancelAnimationFrame(af);
    const st = { ...cur };
    const t0 = performance.now();
    function step(n) {
      const k = Math.min(1, (n - t0) / 480);
      const e = 1 - Math.pow(1 - k, 3);
      totOut.textContent = fmtT(st.total + (t.total - st.total) * e);
      invOut.textContent = fmtT(st.invested + (t.invested - st.invested) * e);
      prfOut.textContent = fmtT(st.profit + (t.profit - st.profit) * e);
      if (k < 1) {
        af = requestAnimationFrame(step);
      } else {
        cur = { ...t };
        totOut.textContent = fmtT(t.total);
        invOut.textContent = fmtT(t.invested);
        prfOut.textContent = fmtT(t.profit);
        af = null;
      }
    }
    af = requestAnimationFrame(step);
  }

  function calc() {
    if (!selected.size) return;
    const a = parseInt(ageSlider.value, 10) || 0;
    const m = Math.max(0, (21 - a) * 12);
    const P = parseA(initIn);
    const PMT = parseA(monIn);
    const r = avgRate();
    const i = (r / 100) / 12;
    let fv = P;
    if (m > 0) {
      fv = i > 0 ? P * Math.pow(1 + i, m) + PMT * ((Math.pow(1 + i, m) - 1) / i) : P + PMT * m;
    }
    const inv = P + PMT * m;
    const prf = Math.max(0, fv - inv);
    const ip = fv > 0 ? Math.round((inv / fv) * 100) : 100;
    const pp = Math.max(0, 100 - ip);
    yldOut.textContent = fmtP(r);
    invSh.textContent = ip + '%';
    prfSh.textContent = pp + '%';
    anim({ total: fv, invested: inv, profit: prf });
  }

  ageSlider.addEventListener('input', updSlider);
  [initIn, monIn].forEach(el => el.addEventListener('input', () => { el.value = fmtN(el.value); }));
  btn.addEventListener('click', calc);

  render();
  updSel();
  updSlider();
  calc();
})();
</script>
@endsection
