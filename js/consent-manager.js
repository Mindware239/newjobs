(function(){
  function getCsrf(){var m=document.querySelector('meta[name="csrf-token"]');return m?m.content:''}
  function get(url){return fetch(url,{credentials:'same-origin'}).then(function(r){return r.json()})}
  function post(url,body){return fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':getCsrf()},body:JSON.stringify(body),credentials:'same-origin'}).then(function(r){return r.json()})}
  function injectCSS(){
    var css = "\
#mw-cookie-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);display:none;align-items:center;justify-content:center;z-index:99999}\
#mw-cookie-modal{width:920px;max-width:95%;background:#fff;border-radius:14px;box-shadow:0 25px 60px rgba(0,0,0,.15);font-family:Inter,system-ui,sans-serif;overflow:hidden;animation:fadeIn .2s ease}\
@keyframes fadeIn{from{transform:translateY(10px);opacity:0}to{transform:translateY(0);opacity:1}}\
.mw-banner{position:fixed;right:24px;bottom:24px;background:#111;border:1px solid rgba(255,255,255,.08);border-radius:18px;box-shadow:0 25px 60px rgba(0,0,0,.45);padding:18px 20px;max-width:420px;color:#fff;z-index:99998;font-family:Inter,system-ui,sans-serif}\
.mw-banner-title{font-size:16px;font-weight:700;margin-bottom:6px;color:#fff}\
.mw-banner-desc{font-size:14px;color:rgba(255,255,255,.85);margin-bottom:12px;line-height:1.5}\
.mw-banner-desc a{color:#9ec5ff;text-decoration:none}\
.mw-banner-desc a:hover{text-decoration:underline}\
.mw-banner-actions{display:flex;flex-direction:column;gap:10px}\
.mw-banner-row{display:flex;gap:10px}\
.mw-header{padding:24px 30px;border-bottom:1px solid #eee}\
.mw-title{font-size:20px;font-weight:700}\
.mw-desc{font-size:14px;color:#555;margin-top:6px}\
.mw-master{display:flex;justify-content:flex-end;padding:15px 30px 0}\
.mw-pill{background:#f3f4f6;padding:6px 12px;border-radius:999px;display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600}\
.mw-switch{width:46px;height:26px;background:#ddd;border-radius:999px;position:relative;cursor:pointer;transition:.2s}\
.mw-switch.active{background:#2563eb}\
.mw-switch span{width:20px;height:20px;background:#fff;border-radius:50%;position:absolute;top:3px;left:3px;transition:.2s}\
.mw-switch.active span{left:23px}\
.mw-section{border-top:1px solid #eee;padding:18px 30px}\
.mw-summary{display:flex;justify-content:space-between;align-items:center;cursor:pointer}\
.mw-summary-left{display:flex;align-items:center;gap:12px}\
.mw-icon{font-weight:700;font-size:18px;color:#2563eb;width:20px}\
.mw-label{font-weight:600;font-size:15px}\
.mw-body{font-size:14px;color:#555;margin-top:12px;line-height:1.6;display:none}\
.mw-body ul{padding-left:20px}\
.mw-footer{padding:20px 30px;border-top:1px solid #eee;text-align:right}\
.mw-btn{padding:10px 18px;border-radius:8px;border:none;font-weight:600;cursor:pointer}\
.mw-btn-primary{background:#2563eb;color:#fff}\
.mw-btn-dark{background:#111827;color:#fff}\
.mw-btn-outline{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.25)}\
.mw-btn-light{background:#fff;color:#111}\
";
    if(!document.getElementById('mw-cookie-style')){var st=document.createElement('style');st.id='mw-cookie-style';st.textContent=css;document.head.appendChild(st)}
  }
  function buildBanner(state){
    injectCSS();
    var banner=document.getElementById('mw-cookie-banner');
    if(!banner){banner=document.createElement('div');banner.id='mw-cookie-banner';banner.className='mw-banner';document.body.appendChild(banner)}
    banner.innerHTML='\
      <div class="mw-banner-title">Cookie settings</div>\
      <div class="mw-banner-desc">We use cookies to deliver and improve our services, analyze site usage, and if you agree, to customize your experience and market our services to you. See our <a href="/cookie/policy">Cookie Policy</a>.</div>\
      <div class="mw-banner-actions">\
        <button id="mw-customize" class="mw-btn mw-btn-outline">Customize Cookie Settings</button>\
        <div class="mw-banner-row">\
          <button id="mw-reject" class="mw-btn mw-btn-dark" style="flex:1">Reject All Cookies</button>\
          <button id="mw-accept" class="mw-btn mw-btn-light" style="flex:1">Accept All Cookies</button>\
        </div>\
      </div>';
    banner.style.display='block';
    var hide=function(){banner.style.display='none'};
    function setLocalConsent(marketing){try{localStorage.setItem('mw_cookie_consent','true');localStorage.setItem('mw_cookie_consent_at',String(Date.now()));localStorage.setItem('mw_cookie_consent_marketing',marketing?'true':'false')}catch(e){}}
    document.getElementById('mw-accept').addEventListener('click',function(){
      var payload={functional:1,analytics:1,marketing:1,performance:1,source:'banner',method:'explicit'};
      post('/cookie/consent',payload).then(function(){setLocalConsent(true);hide();var evt=new CustomEvent('mw:consent-updated',{detail:{consent:{functional:true,analytics:true,marketing:true,performance:true},settings:null}});document.dispatchEvent(evt);})
    });
    document.getElementById('mw-reject').addEventListener('click',function(){
      var payload={functional:0,analytics:0,marketing:0,performance:0,source:'banner',method:'explicit'};
      post('/cookie/consent',payload).then(function(){setLocalConsent(false);hide();var evt=new CustomEvent('mw:consent-updated',{detail:{consent:{functional:false,analytics:false,marketing:false,performance:false},settings:null}});document.dispatchEvent(evt);})
    });
    document.getElementById('mw-customize').addEventListener('click',function(){
      hide();
      buildModal(state);
    });
  }
  function buildModal(state){
    injectCSS();
    var overlay=document.getElementById('mw-cookie-overlay'); if(!overlay){overlay=document.createElement('div');overlay.id='mw-cookie-overlay';document.body.appendChild(overlay)}
    var html = '\
    <div id="mw-cookie-modal">\
      <div class="mw-header">\
        <div class="mw-title">Cookie Preferences</div>\
        <div class="mw-desc">We use cookies to improve your experience and deliver relevant job opportunities. You can allow all cookies or manage them individually below. <a href="/cookie/policy" style="color:#2563eb;text-decoration:none">Learn more</a></div>\
      </div>\
      <div class="mw-master">\
        <div class="mw-pill">Allow All <div id="mw-master-switch" class="mw-switch" role="switch" aria-checked="false"><span></span></div></div>\
      </div>\
      <div class="mw-section">\
        <div class="mw-summary">\
          <div class="mw-summary-left"><div class="mw-icon">-</div><div class="mw-label">Strictly Necessary Cookies</div></div>\
          <small>Always Active</small>\
        </div>\
        <div class="mw-body" style="display:block">These cookies are essential for the operation of the Mindware Infotech Job Portal. They enable secure login, session management, form submissions, and fraud prevention. They cannot be disabled as the website will not function properly without them. These cookies do not store personally identifiable information beyond secure operation.</div>\
      </div>\
      <div class="mw-section" data-key="functional">\
        <div class="mw-summary">\
          <div class="mw-summary-left"><div class="mw-icon">+</div><div class="mw-label">Functional Cookies</div></div>\
          <div class="mw-switch'+(state.functional?' active':'')+'" id="sw-functional" role="switch" aria-checked="'+(state.functional?'true':'false')+'"><span></span></div>\
        </div>\
        <div class="mw-body">These cookies remember:<ul><li>Language selection</li><li>Saved jobs</li><li>Recently viewed jobs</li><li>Personalized dashboard settings</li></ul></div>\
      </div>\
      <div class="mw-section" data-key="analytics">\
        <div class="mw-summary">\
          <div class="mw-summary-left"><div class="mw-icon">+</div><div class="mw-label">Analytics & Performance Cookies</div></div>\
          <div class="mw-switch'+(state.analytics?' active':'')+'" id="sw-analytics" role="switch" aria-checked="'+(state.analytics?'true':'false')+'"><span></span></div>\
        </div>\
        <div class="mw-body">These cookies help us understand how users interact with our platform. They measure page visits, job searches, feature usage, and performance metrics. All data is aggregated and anonymized where possible.</div>\
      </div>\
      <div class="mw-section" data-key="marketing">\
        <div class="mw-summary">\
          <div class="mw-summary-left"><div class="mw-icon">+</div><div class="mw-label">Advertising / Targeting Cookies</div></div>\
          <div class="mw-switch'+(state.marketing?' active':'')+'" id="sw-marketing" role="switch" aria-checked="'+(state.marketing?'true':'false')+'"><span></span></div>\
        </div>\
        <div class="mw-body">These cookies may be set by advertising partners to deliver relevant job advertisements and measure campaign performance across platforms such as LinkedIn, Instagram, Facebook, and Google. If disabled, ads will not be personalized.</div>\
      </div>\
      <div class="mw-section" data-key="performance">\
        <div class="mw-summary">\
          <div class="mw-summary-left"><div class="mw-icon">+</div><div class="mw-label">Performance Cookies</div></div>\
          <div class="mw-switch'+(state.performance?' active':'')+'" id="sw-performance" role="switch" aria-checked="'+(state.performance?'true':'false')+'"><span></span></div>\
        </div>\
        <div class="mw-body">These cookies optimize load speed, reduce server latency, and monitor system health to ensure smooth browsing across devices.</div>\
      </div>\
      <div class="mw-footer"><button id="mw-save" class="mw-btn mw-btn-primary">Save Preferences</button></div>\
    </div>';
    overlay.innerHTML = html;
    overlay.style.display = 'flex';
    var master = document.getElementById('mw-master-switch');
    function setSwitch(id,on){var el=document.getElementById(id.replace('#',''));if(!el)return; if(on){el.classList.add('active');el.setAttribute('aria-checked','true')}else{el.classList.remove('active');el.setAttribute('aria-checked','false')}}
    function getOn(id){var el=document.getElementById(id.replace('#',''));return el && el.classList.contains('active')}
    document.querySelectorAll('.mw-section').forEach(function(sec,idx){
      var sum=sec.querySelector('.mw-summary');var icon=sec.querySelector('.mw-icon');var body=sec.querySelector('.mw-body');
      sum.addEventListener('click',function(e){if(e.target.classList.contains('mw-switch'))return;var open=body.style.display==='block';document.querySelectorAll('.mw-body').forEach(function(b){b.style.display='none'});document.querySelectorAll('.mw-icon').forEach(function(i){i.textContent='+'});if(!open){body.style.display='block';icon.textContent='-'}});
      if(idx===0){icon.textContent='-';body.style.display='block'}
    });
    ['functional','analytics','marketing','performance'].forEach(function(k){var sw=document.getElementById('sw-'+k); if(!sw)return; sw.addEventListener('click',function(e){e.stopPropagation();sw.classList.toggle('active');sw.setAttribute('aria-checked',sw.classList.contains('active')?'true':'false')})});
    master.addEventListener('click',function(){master.classList.toggle('active');var on=master.classList.contains('active');['#sw-functional','#sw-analytics','#sw-marketing','#sw-performance'].forEach(function(id){setSwitch(id,on)})});
    document.getElementById('mw-save').addEventListener('click',function(){
      var payload={functional:getOn('#sw-functional')?1:0,analytics:getOn('#sw-analytics')?1:0,marketing:getOn('#sw-marketing')?1:0,performance:getOn('#sw-performance')?1:0,source:'settings',method:'explicit'};
      post('/cookie/consent',payload).then(function(){try{localStorage.setItem('mw_cookie_consent','true');localStorage.setItem('mw_cookie_consent_at',String(Date.now()));localStorage.setItem('mw_cookie_consent_marketing',payload.marketing===1?'true':'false')}catch(e){} overlay.style.display='none';var evt=new CustomEvent('mw:consent-updated',{detail:{consent:{functional:!!payload.functional,analytics:!!payload.analytics,marketing:!!payload.marketing,performance:!!payload.performance},settings:null}});document.dispatchEvent(evt);})
    })
  }
  function shouldShow(s){
    if(!s) return true;
    var v = s.version || {};
    var c = s.consent || null;
    var hasLocal = false;
    try { hasLocal = localStorage.getItem('mw_cookie_consent') === 'true'; } catch(e){}
    if(hasLocal) return false;
    if(c) return false;
    if(!v || !v.id) return false;
    return true;
  }
  document.addEventListener('DOMContentLoaded',function(){
    get('/cookie/status').then(function(s){
      if(!s) return;
      var state=(s && s.consent)?s.consent:{functional:0,analytics:0,marketing:0,performance:0};
      try{
        if (s && s.consent){
          localStorage.setItem('mw_cookie_consent','true');
          localStorage.setItem('mw_cookie_consent_at',String(Date.now()));
          localStorage.setItem('mw_cookie_consent_marketing',s.consent.marketing===true?'true':'false');
        }
      }catch(e){}
      if(shouldShow(s)){buildBanner(state)}
    })
  })
})();
