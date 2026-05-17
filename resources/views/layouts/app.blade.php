<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title','IT Center Services System')</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/UCC_Logo.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --g900:#0a3323;--g800:#124530;--g700:#18633f;--g600:#1e7d4f;
  --g500:#249660;--g400:#2db877;--g300:#5fce9b;--g200:#a8e8cc;
  --g100:#e4f7ef;--g50:#f2fbf7;
  --white:#fff;--offwhite:#f5f7f6;
  --gray100:#f0f4f2;--gray200:#dde6e2;--gray300:#c5d5cf;
  --gray400:#8aa89f;--gray600:#4d6b61;--gray700:#3d5550;--gray800:#1e3530;
  --red:#e53e3e;--red-bg:#fff0f0;
  --orange:#e67e00;--orange-bg:#fff3e0;
  --blue:#1565c0;--blue-bg:#e3f2fd;
  --purple:#6a1b9a;--purple-bg:#ede7f6;
  --shadow-sm:0 1px 4px rgba(10,51,35,.07);
  --shadow-md:0 4px 18px rgba(10,51,35,.13);
  --shadow-lg:0 16px 48px rgba(10,51,35,.22);
  --r:14px;--rs:8px;
}
html,body{height:100%;font-family:'Plus Jakarta Sans',sans-serif}

/* ── AUTH PAGES (login/register/admin-login) ── */
body.auth-page{
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  padding:20px;overflow:hidden;position:relative;background:var(--g800);
}
.bg-scene{position:fixed;inset:0;z-index:0;background:url('{{ asset("images/UCC_South.webp") }}') center/cover no-repeat}
.bg-scene::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,51,35,.88) 0%,rgba(18,69,48,.82) 40%,rgba(36,150,96,.70) 100%)}
.bg-noise{position:fixed;inset:0;z-index:1;opacity:.03;pointer-events:none;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}

/* ── DASHBOARD PAGES ── */
body.dash-page{background:var(--offwhite);overflow:hidden}

/* ── AUTH CARD ── */
.auth-wrap{
  position:relative;z-index:2;width:100%;max-width:980px;
  display:grid;grid-template-columns:1fr 1fr;
  background:var(--white);border-radius:22px;
  box-shadow:var(--shadow-lg);overflow:hidden;
  max-height:calc(100vh - 40px);
  animation:fadeUp .5s cubic-bezier(.16,1,.3,1) both;
}
.auth-wrap.admin-card{max-width:470px;grid-template-columns:1fr}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}

/* LEFT PANEL */
.panel-left{
  background:linear-gradient(155deg,var(--g800) 0%,var(--g600) 100%);
  padding:36px 34px;display:flex;flex-direction:column;
  position:relative;overflow:hidden;
}
.panel-left::before{content:'';position:absolute;bottom:-70px;right:-70px;width:240px;height:240px;border-radius:50%;background:rgba(255,255,255,.05);pointer-events:none}
.panel-left::after{content:'';position:absolute;top:-40px;left:-40px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.04);pointer-events:none}
.logo-row{display:flex;align-items:center;gap:12px;margin-bottom:24px}
.logo-row img{width:48px;height:48px;border-radius:10px;background:rgba(255,255,255,.12);padding:4px;object-fit:contain;cursor:pointer;user-select:none;flex-shrink:0}
.logo-text .sys-name{font-size:.95rem;font-weight:800;color:var(--white);line-height:1.2}
.logo-text .sys-sub{font-size:.72rem;color:rgba(255,255,255,.65);margin-top:2px}
.left-title{font-size:1.55rem;font-weight:800;color:var(--white);line-height:1.2;margin-bottom:10px}
.left-desc{font-size:.8rem;color:rgba(255,255,255,.68);line-height:1.65;margin-bottom:auto}
.feat-list{display:flex;flex-direction:column;gap:8px;margin-top:22px;margin-bottom:22px}
.feat-item{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.09);border-radius:var(--rs);padding:10px 12px}
.feat-icon{width:32px;height:32px;flex-shrink:0;background:rgba(255,255,255,.14);border-radius:7px;display:flex;align-items:center;justify-content:center;color:var(--white);font-size:.82rem}
.feat-title{font-size:.78rem;font-weight:700;color:var(--white)}
.feat-sub{font-size:.69rem;color:rgba(255,255,255,.6);margin-top:1px}
.stats-row{display:flex;border-top:1px solid rgba(255,255,255,.12);padding-top:16px;margin-top:auto}
.stat{flex:1;text-align:center}
.stat .sv{font-size:.9rem;font-weight:800;color:var(--white)}
.stat .sl{font-size:.65rem;color:rgba(255,255,255,.55);margin-top:2px}
.stat+.stat{border-left:1px solid rgba(255,255,255,.12)}

/* RIGHT PANEL */
.panel-right{padding:36px 38px 28px;display:flex;flex-direction:column;background:var(--white);overflow-y:auto;scrollbar-width:none}
.panel-right::-webkit-scrollbar{display:none}
.form-hd{margin-bottom:20px}
.form-hd h2{font-size:1.45rem;font-weight:800;color:var(--gray800)}
.form-hd p{font-size:.78rem;color:var(--gray600);margin-top:3px}

/* FORM ELEMENTS */
.fg{margin-bottom:13px}
.flabel{display:flex;align-items:center;gap:6px;font-size:.74rem;font-weight:600;color:var(--gray600);margin-bottom:5px}
.flabel i{color:var(--g600);font-size:.75rem}
.fc,.fs{width:100%;padding:10px 13px;border:1.5px solid var(--gray200);border-radius:var(--rs);font-family:inherit;font-size:.82rem;color:var(--gray800);background:var(--gray100);outline:none;transition:border-color .2s,box-shadow .2s,background .2s;appearance:none}
.fc:focus,.fs:focus{border-color:var(--g500);background:var(--white);box-shadow:0 0 0 3px rgba(36,150,96,.12)}
.fc::placeholder{color:var(--gray400)}
.iw{position:relative}
.iw .ii{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--gray400);font-size:.78rem;pointer-events:none}
.iw .fc{padding-left:32px}
.iw .eye-btn{position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray400);cursor:pointer;font-size:.78rem;padding:3px}
.iw .eye-btn:hover{color:var(--g600)}
.sw{position:relative}
.sw::after{content:'\f107';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:13px;top:50%;transform:translateY(-50%);color:var(--gray400);pointer-events:none;font-size:.75rem}
.btn{width:100%;padding:12px;background:linear-gradient(135deg,var(--g700) 0%,var(--g500) 100%);color:var(--white);border:none;border-radius:var(--rs);font-family:inherit;font-size:.87rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;box-shadow:0 4px 14px rgba(30,125,79,.3);transition:opacity .2s,transform .15s;margin-top:4px}
.btn:hover{opacity:.92;transform:translateY(-1px)}
.btn:active{transform:translateY(0)}
.divider{display:flex;align-items:center;gap:10px;margin:14px 0 10px;color:var(--gray400);font-size:.72rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--gray200)}
.form-foot{text-align:center;font-size:.77rem;color:var(--gray600)}
.form-foot a{color:var(--g700);font-weight:700;text-decoration:none}
.form-foot a:hover{text-decoration:underline}
.abox{border-radius:var(--rs);padding:10px 12px;font-size:.77rem;display:flex;align-items:flex-start;gap:9px;margin-bottom:14px}
.abox.info{background:var(--g100);border-left:3px solid var(--g400);color:var(--g800)}
.abox.warn{background:#fff8e1;border-left:3px solid #f5a623;color:#7a5200}
.abox.err{background:var(--red-bg);border-left:3px solid var(--red);color:#7a1212}
.abox.ok{background:var(--g100);border-left:3px solid var(--g500);color:var(--g800)}
.str-bar{display:flex;gap:4px;margin-top:6px}
.str-seg{flex:1;height:3px;border-radius:2px;background:var(--gray200);transition:background .3s}
.str-seg.s1{background:var(--red)}.str-seg.s2{background:#f5a623}.str-seg.s3{background:var(--g400)}.str-seg.s4{background:var(--g500)}
.str-txt{font-size:.69rem;color:var(--gray400);margin-top:4px;text-align:right}
.cb-row{display:flex;align-items:center;gap:8px;font-size:.77rem;color:var(--gray600);cursor:pointer;margin-bottom:4px}
.cb-row input[type=checkbox]{width:15px;height:15px;accent-color:var(--g600);cursor:pointer}
.cb-row a{color:var(--g700);font-weight:600;text-decoration:none}
.pic-upload{display:flex;align-items:center;gap:14px;border:1.5px dashed var(--gray200);border-radius:var(--rs);padding:12px 14px;cursor:pointer;transition:border-color .2s;background:var(--gray100)}
.pic-upload:hover{border-color:var(--g400);background:var(--g50)}
.pic-preview{width:48px;height:48px;border-radius:50%;background:var(--gray200);overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:var(--gray400);font-size:1.2rem}
.pic-preview img{width:100%;height:100%;object-fit:cover}
.pic-txt .pt1{font-size:.78rem;font-weight:600;color:var(--g700)}
.pic-txt .pt2{font-size:.68rem;color:var(--gray400);margin-top:2px}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.cpr{text-align:center;font-size:.67rem;color:var(--gray400);margin-top:16px;line-height:1.7}
.admin-head{text-align:center;padding:32px 36px 0}
.admin-head img{width:64px;height:64px;border-radius:50%;border:3px solid var(--g200);object-fit:contain;margin-bottom:10px;cursor:pointer;user-select:none}
.admin-head h2{font-size:1.3rem;font-weight:800;color:var(--gray800)}
.admin-head p{font-size:.75rem;color:var(--gray600);margin:3px 0 8px}
.badge-pill{display:inline-block;background:linear-gradient(135deg,var(--g700),var(--g500));color:var(--white);font-size:.68rem;font-weight:700;padding:4px 12px;border-radius:20px;margin-bottom:4px}
.back-link{display:flex;align-items:center;gap:6px;justify-content:center;font-size:.76rem;color:var(--g700);text-decoration:none;font-weight:600;margin-top:12px}
.back-link:hover{text-decoration:underline}

/* ── DASHBOARD LAYOUT ── */
.dash-wrap{display:flex;height:100vh;width:100vw;overflow:hidden;animation:fadeUp .4s ease both}
.sidebar{width:235px;flex-shrink:0;background:linear-gradient(180deg,var(--g900) 0%,var(--g800) 100%);display:flex;flex-direction:column;overflow-y:auto;scrollbar-width:none}
.sidebar::-webkit-scrollbar{display:none}
.sb-brand{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px}
.sb-brand img{width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.1);padding:3px;object-fit:contain;flex-shrink:0}
.sb-brand .sbn{font-size:.82rem;font-weight:800;color:var(--white);line-height:1.2}
.sb-brand .sbv{font-size:.62rem;color:rgba(255,255,255,.45);margin-top:2px}
.sb-user{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px}
.sb-avatar{width:38px;height:38px;border-radius:50%;flex-shrink:0;overflow:hidden;background:var(--g500);display:flex;align-items:center;justify-content:center;color:var(--white);font-weight:800;font-size:.9rem}
.sb-avatar img{width:100%;height:100%;object-fit:cover}
.sb-uname{font-size:.78rem;font-weight:700;color:var(--white);line-height:1.2}
.sb-uid{font-size:.64rem;color:rgba(255,255,255,.5);margin-top:1px}
.sb-badge{display:inline-block;margin-top:4px;background:rgba(255,255,255,.13);color:var(--white);font-size:.6rem;font-weight:700;padding:2px 8px;border-radius:10px}
.sb-nav{flex:1;padding:12px 0}
.sb-link{display:flex;align-items:center;gap:10px;padding:9px 18px;font-size:.78rem;font-weight:600;color:rgba(255,255,255,.6);text-decoration:none;transition:background .2s,color .2s;position:relative;border:none;cursor:pointer;background:none;width:100%}
.sb-link:hover{background:rgba(255,255,255,.07);color:var(--white)}
.sb-link.active{background:rgba(255,255,255,.11);color:var(--white);border-right:3px solid var(--g300)}
.sb-link i{width:16px;text-align:center;font-size:.8rem}
.sb-link .nb{margin-left:auto;background:var(--red);color:var(--white);font-size:.6rem;font-weight:800;padding:2px 6px;border-radius:10px}
.sb-section{padding:8px 18px 4px;font-size:.63rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.06em}
.sb-footer{padding:12px 18px;border-top:1px solid rgba(255,255,255,.08);font-size:.63rem;color:rgba(255,255,255,.35);line-height:1.6}
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;background:var(--offwhite)}
.topbar{background:var(--white);padding:14px 24px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--gray200);flex-shrink:0}
.topbar h1{font-size:1.2rem;font-weight:800;color:var(--gray800)}
.topbar p{font-size:.74rem;color:var(--gray600);margin-top:2px}
.topbar-right{display:flex;align-items:center;gap:12px}
.clock{display:flex;align-items:center;gap:6px;background:var(--g100);color:var(--g700);font-size:.76rem;font-weight:700;padding:6px 12px;border-radius:20px}
.content{padding:20px 24px;flex:1;overflow-y:auto}
.content::-webkit-scrollbar{width:5px}
.content::-webkit-scrollbar-thumb{background:var(--gray300);border-radius:3px}

/* stat cards */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}
.stat-card{background:var(--white);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:12px;box-shadow:var(--shadow-sm);border:1.5px solid var(--gray200);transition:transform .2s,box-shadow .2s}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.stat-ico{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.stat-lbl{font-size:.7rem;color:var(--gray600);margin-bottom:2px;font-weight:600}
.stat-val{font-size:1.45rem;font-weight:800;color:var(--gray800)}

/* quick-action cards */
.qa-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
.qa-card{background:var(--white);border-radius:12px;padding:18px 14px;text-align:center;cursor:pointer;text-decoration:none;border:1.5px solid var(--gray200);box-shadow:var(--shadow-sm);transition:transform .2s,box-shadow .2s}
.qa-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.qa-ico{font-size:1.4rem;margin-bottom:7px}
.qa-lbl{font-size:.78rem;font-weight:700;color:var(--gray700)}

/* table */
.section-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.section-hd h3{font-size:.9rem;font-weight:800;color:var(--gray800);display:flex;align-items:center;gap:7px}
.section-hd a{font-size:.74rem;color:var(--g600);font-weight:700;text-decoration:none}
.tbl-wrap{background:var(--white);border-radius:12px;overflow:hidden;box-shadow:var(--shadow-sm);border:1.5px solid var(--gray200)}
table{width:100%;border-collapse:collapse}
th{background:var(--g50);padding:9px 13px;font-size:.68rem;font-weight:700;color:var(--gray600);text-align:left;border-bottom:1.5px solid var(--gray200);white-space:nowrap}
td{padding:10px 13px;font-size:.76rem;color:var(--gray800);border-bottom:1px solid var(--gray100)}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--g50)}

/* tags */
.tag{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:7px;font-size:.66rem;font-weight:700;white-space:nowrap}
.tag-print{background:var(--blue-bg);color:var(--blue)}
.tag-copy{background:var(--orange-bg);color:var(--orange)}
.tag-res{background:var(--g100);color:var(--g700)}
.tag-pend{background:var(--orange-bg);color:var(--orange)}
.tag-active{background:var(--g100);color:var(--g700)}
.tag-done{background:var(--g100);color:var(--g700)}
.tag-appr{background:var(--blue-bg);color:var(--blue)}
.tag-rej{background:var(--red-bg);color:var(--red)}
.tag-deact{background:#f3e5f5;color:#7b1fa2}
.tag-arch{background:var(--gray100);color:var(--gray600)}
.tag-student{background:var(--blue-bg);color:var(--blue)}
.tag-faculty{background:var(--purple-bg);color:var(--purple)}

/* action buttons */
.act-btn{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;border:none;cursor:pointer;font-size:.72rem;transition:opacity .2s,transform .15s}
.act-btn:hover{opacity:.85;transform:scale(1.08)}
.act-view{background:var(--g500);color:var(--white)}
.act-edit{background:var(--blue);color:var(--white)}
.act-appr{background:var(--g500);color:var(--white)}
.act-deact{background:#7b1fa2;color:var(--white)}
.act-arch{background:var(--gray600);color:var(--white)}
.act-del{background:var(--red);color:var(--white)}
.act-actv{background:var(--orange);color:var(--white)}

/* verify / deactivated states */
.verify-state{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:50px 40px;text-align:center;flex:1}
.verify-state .vi{width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin-bottom:14px}
.verify-state h3{font-size:1rem;font-weight:800;color:var(--gray800);margin-bottom:7px}
.verify-state p{font-size:.78rem;color:var(--gray600);max-width:340px;line-height:1.7}

/* filter bar */
.filter-bar{background:var(--white);border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:var(--shadow-sm);border:1.5px solid var(--gray200)}
.filter-bar .fc,.filter-bar .fs{max-width:180px;font-size:.78rem;padding:8px 11px}
.filter-bar .btn-sm{padding:8px 16px;font-size:.76rem;font-weight:700;background:linear-gradient(135deg,var(--g700),var(--g500));color:var(--white);border:none;border-radius:var(--rs);cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap}
.filter-bar .btn-outline{padding:8px 14px;font-size:.76rem;font-weight:700;background:var(--white);color:var(--g700);border:1.5px solid var(--g300);border-radius:var(--rs);cursor:pointer}

/* status tab pills */
.tab-pills{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.tab-pill{padding:6px 14px;border-radius:20px;font-size:.74rem;font-weight:700;cursor:pointer;border:1.5px solid var(--gray200);background:var(--white);color:var(--gray600);text-decoration:none;transition:all .2s}
.tab-pill:hover,.tab-pill.active{background:var(--g700);color:var(--white);border-color:var(--g700)}
.tab-pill .cnt{background:rgba(255,255,255,.25);padding:1px 6px;border-radius:8px;font-size:.65rem;margin-left:5px}

/* modal */
.modal-bg{display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px}
.modal-bg.open{display:flex}
.modal-box{background:var(--white);border-radius:16px;width:100%;max-width:520px;max-height:85vh;display:flex;flex-direction:column;box-shadow:var(--shadow-lg);animation:fadeUp .3s ease both}
.modal-hd{padding:18px 22px 14px;border-bottom:1px solid var(--gray200);display:flex;align-items:center;justify-content:space-between}
.modal-hd h3{font-size:.95rem;font-weight:800;color:var(--gray800)}
.modal-close{background:none;border:none;color:var(--gray400);cursor:pointer;font-size:1rem;padding:4px}
.modal-close:hover{color:var(--gray800)}
.modal-body{padding:18px 22px;overflow-y:auto;font-size:.79rem;color:var(--gray600);line-height:1.75}
.modal-body h4{font-size:.82rem;font-weight:700;color:var(--gray800);margin:12px 0 5px}
.modal-body p{margin-bottom:9px}
.modal-body ol,.modal-body ul{padding-left:17px;margin-bottom:9px}
.modal-footer{padding:14px 22px;border-top:1px solid var(--gray200);display:flex;gap:8px;justify-content:flex-end}
.modal-btn{padding:9px 20px;border-radius:var(--rs);font-family:inherit;font-size:.8rem;font-weight:700;cursor:pointer;border:none;transition:opacity .2s}
.modal-btn.primary{background:linear-gradient(135deg,var(--g700),var(--g500));color:var(--white)}
.modal-btn.danger{background:var(--red);color:var(--white)}
.modal-btn.secondary{background:var(--gray100);color:var(--gray700)}
.modal-btn:hover{opacity:.88}

/* notification bell */
.notif-wrap{position:relative;cursor:pointer}
.notif-badge{position:absolute;top:-5px;right:-5px;background:var(--red);color:var(--white);font-size:.58rem;font-weight:800;padding:2px 5px;border-radius:10px;min-width:18px;text-align:center}

/* profile page */
.profile-card{background:var(--white);border-radius:14px;box-shadow:var(--shadow-sm);border:1.5px solid var(--gray200);overflow:hidden;margin-bottom:16px}
.profile-card-hd{padding:14px 18px;border-bottom:1px solid var(--gray200);font-size:.85rem;font-weight:800;color:var(--gray800);display:flex;align-items:center;gap:8px}
.profile-card-hd i{color:var(--g600)}
.profile-card-body{padding:18px}

@media(max-width:768px){
  .auth-wrap{grid-template-columns:1fr;max-width:460px}
  .panel-left{display:none}
  .stat-grid,.qa-grid{grid-template-columns:repeat(2,1fr)}
  .sidebar{width:200px}
}

/* ── MOBILE RESPONSIVE ── */
@media(max-width:768px){
  /* Auth pages */
  .auth-wrap{grid-template-columns:1fr!important;max-width:100%!important;border-radius:16px;max-height:none!important}
  .panel-left{display:none!important}
  .panel-right{padding:28px 22px 24px!important}
  .auth-wrap.admin-card{max-width:100%!important}

  /* Dashboard */
  .dash-wrap{flex-direction:column;height:auto;min-height:100vh}
  .sidebar{width:100%!important;flex-direction:row;flex-wrap:wrap;padding:12px;height:auto}
  .sb-brand{padding:8px 12px 8px;border-bottom:none;border-right:1px solid rgba(255,255,255,.08)}
  .sb-user{padding:8px 12px;border-bottom:none}
  .sb-nav{display:none}  /* Hidden on mobile — use hamburger */
  .sb-footer{display:none}
  .main{height:auto;overflow:visible}
  .content{padding:14px 16px}
  .topbar{padding:12px 16px;flex-wrap:wrap;gap:8px}
  .topbar h1{font-size:1rem}

  /* Stat grids */
  .stat-grid{grid-template-columns:repeat(2,1fr)!important}
  .qa-grid{grid-template-columns:repeat(2,1fr)!important}

  /* Tables — horizontal scroll */
  .tbl-wrap{overflow-x:auto}
  table{min-width:600px}

  /* Forms */
  .g2{grid-template-columns:1fr!important}
  .filter-bar{flex-direction:column;align-items:stretch}
  .filter-bar .fc,.filter-bar .fs{max-width:100%!important}
  .tab-pills{gap:5px}
  .tab-pill{font-size:.68rem;padding:5px 10px}

  /* Profile grid */
  [style*="grid-template-columns:320px"]{grid-template-columns:1fr!important}
  [style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}
}

/* Mobile sidebar hamburger */
.sb-toggle{
  display:none;
  background:none;border:none;color:rgba(255,255,255,.8);
  font-size:1.2rem;cursor:pointer;padding:8px;
}
@media(max-width:768px){
  .sb-toggle{display:flex;align-items:center;justify-content:center}
  .sb-nav.mobile-open{display:flex!important;flex-direction:column;width:100%;order:10;border-top:1px solid rgba(255,255,255,.1);margin-top:8px;padding-top:8px}
}

/* User notification toast */
#user-toast-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column-reverse;
  gap: 8px;
  max-width: 320px;
  pointer-events: none;
}
.user-toast {
  pointer-events: auto;
  background: var(--white);
  border-radius: 12px;
  box-shadow: 0 8px 28px rgba(10,51,35,.18);
  padding: 12px 14px;
  border-left: 4px solid var(--g500);
  display: flex;
  gap: 10px;
  align-items: flex-start;
  animation: fadeUp .35s cubic-bezier(.16,1,.3,1);
  transition: opacity .3s;
}
</style>
@stack('styles')
</head>
<body class="@yield('body-class','auth-page')">
@if(View::hasSection('auth-bg'))
<div class="bg-scene"></div>
<div class="bg-noise"></div>
@endif
@yield('content')
@stack('scripts')
</body>
</html>