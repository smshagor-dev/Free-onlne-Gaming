<style>
/*
 * Free Games player dashboard.
 * Mirrors the approved dark gaming reference while keeping every panel bound
 * to the existing authenticated user's data, routes, and CMS footer settings.
 */
.fg-page.player-dashboard-route {
    background:radial-gradient(ellipse at 65% -14%,#102845 0,#091627 48%,#081422 90%);
}
.player-dashboard-route .fg-topbar {background:linear-gradient(180deg,#081726,#091526)}
.player-dashboard-route .fg-nav {min-height:60px}
.player-dashboard-route .fg-category-bar {display:none}
.player-dashboard-route .site-main {
    max-width:1536px;
    margin:0 auto;
    padding:0 clamp(22px,3.9vw,60px) 12px;
}
.player-dashboard-route .account-nav {
    display:flex;
    align-items:stretch;
    flex-wrap:wrap;
    gap:4px;
    min-height:51px;
    padding:0 7px;
    margin:0 0 19px;
    border-bottom:1px solid #21364d;
    font-size:13px;
    font-weight:700;
}
.player-dashboard-route .account-nav>a,
.player-dashboard-route .account-nav>form>button {
    position:relative;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    min-height:50px;
    padding:0 clamp(9px,1.2vw,19px);
    color:#d9e8f7;
    border:0;
    font-weight:700;
    background:transparent;
    white-space:nowrap;
    transition:color .18s;
}
.player-dashboard-route .account-nav a:hover,
.player-dashboard-route .account-nav button:hover {color:#56caff}
.player-dashboard-route .account-nav .is-active {
    color:#fff;
}
.player-dashboard-route .account-nav .is-active:after {
    position:absolute;
    content:"";
    height:3px;
    left:7px;
    right:7px;
    bottom:0;
    border-radius:6px 6px 0 0;
    background:#2ebeff;
    box-shadow:0 0 12px #1e99ff40;
}
.player-dashboard-route .account-nav i {font-size:17px;font-weight:400}
.player-dashboard-route .account-nav .is-active i {color:#36baff}
.player-dashboard-route .account-nav form {display:flex;align-items:stretch;margin:0}
.player-dashboard-route .account-nav button {cursor:pointer;font:inherit}

.player-dashboard {
    --player-muted:#b7c9df;
    --player-line:#284565;
    --player-cyan:#40baff;
    width:100%;
    min-width:0;
    color:#f3f8ff;
    font-size:14px;
}
.player-dashboard a {text-decoration:none}
.player-dashboard a:focus-visible,
.player-dashboard button:focus-visible {
    outline:2px solid #79d5ff;
    outline-offset:4px;
}
.player-welcome {
    position:relative;
    isolation:isolate;
    display:flex;
    align-items:center;
    justify-content:space-between;
    min-height:238px;
    padding:18px clamp(22px,3vw,44px);
    border:1px solid #147fce;
    border-radius:14px;
    background:
        radial-gradient(ellipse at 78% 47%,#075ca051,transparent 48%),
        linear-gradient(115deg,#09294c 0,#0b2449 40%,#081a35 74%,#0b3160);
    box-shadow:inset 0 1px 0 #76c4ff15;
    overflow:hidden;
}
.player-welcome:before {
    position:absolute;
    content:"";
    inset:-100px 18% -90px 30%;
    z-index:-1;
    transform:skewX(-33deg);
    border-left:1px solid #366eaf21;
    border-right:1px solid #438cda1b;
    background:linear-gradient(90deg,transparent,#1465b518 37%,transparent 68%);
    pointer-events:none;
}
.player-welcome-copy {
    position:relative;
    z-index:2;
    flex:0 1 56%;
    min-width:0;
    max-width:660px;
}
.player-eyebrow {
    display:inline-flex;
    align-items:center;
    gap:9px;
    color:#58c6ff;
    font-size:13px;
    font-weight:850;
    letter-spacing:.085em;
    line-height:1.35;
}
.player-welcome h1 {
    margin:9px 0 8px;
    color:#f1f7ff;
    font-size:clamp(29px,3.1vw,43px);
    line-height:1.075;
    letter-spacing:-.042em;
    font-weight:850;
    overflow-wrap:anywhere;
}
.player-welcome h1 span {color:#29bfff}
.player-welcome p {
    max-width:640px;
    margin:0 0 15px;
    color:#d3e2f5;
    font-size:clamp(13px,1.13vw,16px);
    line-height:1.6;
}
.player-primary {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:14px;
    min-height:44px;
    padding:10px 23px;
    border:1px solid #169eff;
    border-radius:10px;
    background:linear-gradient(110deg,#0fa9fc,#087df2);
    color:#fff!important;
    font-size:15px;
    font-weight:850;
    box-shadow:0 8px 22px #008bff20;
    transition:transform .2s,filter .2s;
}
.player-primary:hover {filter:brightness(1.1);transform:translateY(-2px)}
.player-primary i {font-size:12px}
.player-art {
    position:absolute;
    top:50%;
    right:clamp(-55px,-1.5vw,-12px);
    z-index:1;
    width:min(49%,590px);
    transform:translateY(-52%);
    text-align:center;
    pointer-events:none;
}
.player-art img {
    display:block;
    width:100%;
    height:auto;
    aspect-ratio:2/1;
    object-fit:contain;
    filter:drop-shadow(0 12px 15px #00143055);
}
.art-label {
    display:block;
    margin:-32px 0 0;
    padding-left:6px;
    color:#8bd3ff;
    font-size:12px;
    font-weight:850;
    letter-spacing:.145em;
    text-shadow:0 0 14px #007ef966;
}
.player-stats {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:17px;
    margin:17px 0 16px;
}
.player-stat {
    display:flex;
    align-items:center;
    gap:17px;
    min-height:70px;
    min-width:0;
    padding:10px 22px;
    border:1px solid #456188;
    border-radius:13px;
    background:linear-gradient(115deg,#171d35,#0f243d);
    transition:border-color .2s,transform .2s,box-shadow .2s;
}
.player-stat:first-child {border-color:#64527d;background:linear-gradient(115deg,#211a35,#101f37)}
.player-stat:nth-child(2) {border-color:#645f9a;background:linear-gradient(115deg,#1b1b39,#11263f)}
.player-stat:hover {transform:translateY(-2px);border-color:#5baff4;box-shadow:0 9px 24px #0002}
.stat-icon {
    display:grid;
    flex:0 0 49px;
    place-items:center;
    width:49px;
    height:45px;
    color:#48b8ff;
    font-size:30px;
}
.stat-icon.pink {color:#f56bbb}
.stat-icon.purple {color:#b97afc}
.stat-icon.blue {color:#4cbdff}
.stat-icon.gold {color:#ffbf31}
.stat-copy {display:flex;flex-direction:column;gap:1px;min-width:0}
.stat-copy strong {
    color:#f8faff;
    font-size:24px;
    font-weight:850;
    line-height:1.1;
    font-variant-numeric:tabular-nums;
}
.stat-copy>span {font-size:13px;color:#dde7f7;white-space:nowrap}
.stat-arrow {font-size:10px;color:#69baf5;margin-left:auto;opacity:0}
.player-stat:hover .stat-arrow {opacity:1}
.player-columns {
    display:grid;
    grid-template-columns:minmax(0,2.2fr) minmax(320px,1fr);
    align-items:start;
    gap:19px;
}
.player-feed {
    min-width:0;
    padding:17px 19px 12px;
    border:1px solid #263e5b;
    border-radius:15px;
    background:linear-gradient(135deg,#10243b,#0d1b2d);
}
.player-section + .player-section {
    margin-top:15px;
    padding-top:13px;
    border-top:1px solid #20364d;
}
.player-section-heading {
    display:flex;
    align-items:start;
    justify-content:space-between;
    gap:12px;
    margin:0 0 13px;
}
.player-dashboard h2 {
    margin:0;
    color:#f7f9ff;
    font-size:clamp(18px,1.65vw,23px);
    line-height:1.2;
    font-weight:850;
    letter-spacing:-.035em;
}
.player-section-heading p {
    margin:4px 0 0;
    color:#d2dfef;
    font-size:13px;
    line-height:1.45;
}
.player-section-heading>a {
    display:inline-flex;
    align-items:center;
    gap:10px;
    padding-top:3px;
    color:#46c0ff!important;
    white-space:nowrap;
    font-size:13px;
    font-weight:800;
}
.player-section-heading>a:hover,.player-empty>a:hover {color:#9ce6ff!important;text-decoration:underline}
.player-section-heading>a i,.player-empty>a i {font-size:13px}
.player-empty {
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    gap:5px;
    min-height:155px;
    padding:14px 18px;
    border:1px dashed #47769a;
    border-radius:11px;
    background:linear-gradient(125deg,#101f33,#0e2138);
    text-align:center;
}
.empty-symbol {
    display:block;
    margin:0 0 6px;
    font-size:34px;
    line-height:1;
    color:#61c5ff;
}
.empty-symbol.purple {color:#b875fb}
.player-empty h3 {margin:0;color:#fff;font-size:15px;font-weight:850;line-height:1.3}
.player-empty p {margin:0;color:#c5d4e7;font-size:13px;line-height:1.45}
.player-empty>a {
    display:inline-flex;
    align-items:center;
    gap:7px;
    color:#39bfff!important;
    margin-top:5px;
    font-size:13px;
    font-weight:850;
}
.player-games {display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.player-game {
    min-width:0;
    border:1px solid #34516f;
    border-radius:10px;
    overflow:hidden;
    background:#142940;
    transition:border-color .2s,transform .2s;
}
.player-game:hover {transform:translateY(-3px);border-color:#44baff}
.player-game img {display:block;width:100%;aspect-ratio:4/3;object-fit:cover;background:#1b3151}
.player-game>div {padding:10px}
.player-game h3 {margin:0 0 6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px}
.player-game span {color:#64c6ff;font-size:11px}
.player-sidebar {display:flex;flex-direction:column;gap:11px;min-width:0}
.player-lottery {
    position:relative;
    min-width:0;
    overflow:hidden;
    padding:17px 23px 13px;
    border:1px solid #d29718;
    border-radius:14px;
    background:
        radial-gradient(circle at 100% 0%,#a2712833,transparent 60%),
        linear-gradient(135deg,#1e2430,#192231);
}
.player-lottery .player-eyebrow {position:relative;z-index:1;color:#ffbf2e;font-size:13px;letter-spacing:.02em}
.player-lottery .player-eyebrow i {font-size:20px}
.player-lottery-watermark {
    position:absolute;
    z-index:0;
    top:12px;
    right:18px;
    color:#eeb84a;
    opacity:.19;
    font-size:58px;
    transform:rotate(12deg);
}
.player-lottery h2 {position:relative;margin-top:7px;font-size:21px;line-height:1.2}
.player-lottery p {
    position:relative;
    margin:4px 0 10px;
    font-size:13px;
    line-height:1.55;
    color:#d7e0ec;
}
.lottery-summary {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:9px;
    margin:0 0 9px;
}
.lottery-metric {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:15px;
    min-height:57px;
    padding:8px;
    border:1px solid #896c2d;
    border-radius:10px;
    background:#202a34bd;
}
.lottery-metric:last-child {background:#292c2e}
.lottery-metric>i {color:#ffc52f;font-size:25px}
.lottery-metric>span {display:flex;flex-direction:column}
.lottery-metric strong {font-size:22px;line-height:1.05;font-weight:850;color:#fff}
.lottery-metric small {font-size:12px;line-height:1.35;color:#e5e7e6}
.player-secondary {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:13px;
    width:100%;
    min-height:35px;
    padding:8px 10px;
    border-radius:9px;
    color:#191e2b!important;
    background:linear-gradient(90deg,#ffc23c,#ffd05c);
    font-size:13px;
    font-weight:850;
    transition:filter .2s;
}
.player-secondary:hover {filter:brightness(1.08)}
.lottery-results {
    display:block;
    width:max-content;
    max-width:100%;
    margin:9px auto 0;
    color:#56c4ff!important;
    font-size:12px;
    text-decoration:underline!important;
    text-underline-offset:2px;
}
.player-account {
    padding:14px 22px 8px;
    border:1px solid #426891;
    border-radius:14px;
    background:linear-gradient(140deg,#10243b,#0f2035);
}
.player-account h2 {
    display:flex;
    align-items:center;
    gap:12px;
    padding:0 0 10px;
    border-bottom:1px solid #26405c;
    font-size:18px;
}
.player-account h2>i {color:#c3e7ff;font-size:20px}
.player-account>a {
    display:grid;
    grid-template-columns:34px minmax(0,1fr) 18px;
    align-items:center;
    gap:10px;
    min-height:54px;
    padding:8px 0;
    border-bottom:1px solid #233b53;
    font-size:13px;
    font-weight:750;
}
.player-account>a:last-child {border-bottom:0}
.player-account>a:hover>span {color:#78cbff}
.player-account .account-link-icon {color:#31b5ff;font-size:23px;text-align:center}
.player-account>a:nth-of-type(3) .account-link-icon {color:#89d3ff}
.player-account small {
    display:block;
    margin-top:3px;
    color:#bbcfe3;
    font-size:11px;
    font-weight:400;
    line-height:1.4;
}
.player-account .account-arrow {margin-left:auto;color:#b9d7f3;font-size:13px}
.player-dashboard-route .site-footer {
    display:flex;
    align-items:center;
    justify-content:flex-start;
    flex-wrap:wrap;
    gap:21px;
    width:100%;
    max-width:1536px;
    padding:9px clamp(22px,3.9vw,60px) 24px;
    margin:0 auto;
    border-top:0;
    font-size:11px;
    color:#bed0e4;
    text-align:left;
}
.dashboard-footer-brand {
    display:inline-flex;
    align-items:center;
    gap:5px;
    color:#f5faff;
    font-size:13px;
    font-weight:800;
}
.dashboard-footer-brand>i {font-size:23px;color:#40c5ff;margin-right:4px}
.dashboard-footer-brand>span {color:#52beff;margin-left:-5px}
.dashboard-footer-separator {width:1px;height:16px;background:#4e6983}
.dashboard-footer-links {display:inline-flex;flex-wrap:wrap;gap:5px;align-items:center}
.dashboard-footer-links a {color:#d3e6ff!important}
.dashboard-footer-links a:hover {color:#58c3ff!important}

@media(max-width:1190px) {
    .player-dashboard-route .site-main {padding-left:24px;padding-right:24px}
    .player-dashboard-route .site-footer {padding-left:24px;padding-right:24px}
    .player-welcome-copy {max-width:58%}
    .player-art {width:52%;right:-31px}
    .player-columns {grid-template-columns:minmax(0,1.8fr) minmax(285px,1fr);gap:14px}
    .player-stats {gap:11px}
    .player-stat {padding:12px 13px;gap:7px}
    .stat-icon {width:41px;flex-basis:41px}
    .player-lottery {padding:18px 15px 13px}
    .player-account {padding:14px 15px 8px}
    .player-games {grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:900px) {
    .player-dashboard-route .account-nav {overflow-x:auto;flex-wrap:nowrap;scrollbar-width:none}
    .player-dashboard-route .account-nav::-webkit-scrollbar {display:none}
    .player-dashboard-route .account-nav>a,
    .player-dashboard-route .account-nav>form>button {padding:0 12px;font-size:12px}
    .player-stats {grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .player-columns {grid-template-columns:1fr}
    .player-sidebar {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start;gap:13px}
    .player-account {min-height:100%}
    .player-welcome {min-height:220px}
    .player-welcome h1 {font-size:clamp(29px,4.2vw,38px)}
    .player-art {width:53%;right:-25px}
}
@media(max-width:650px) {
    .player-dashboard-route .site-main {padding:0 14px 10px}
    .player-dashboard-route .site-footer {padding:12px 14px 23px;gap:9px}
    .player-dashboard-route .account-nav {margin-bottom:15px;padding-left:0}
    .player-welcome {min-height:0;padding:28px 22px;border-radius:12px}
    .player-welcome-copy {max-width:100%;flex-basis:100%}
    .player-welcome h1 {font-size:clamp(27px,7vw,38px);line-height:1.1}
    .player-welcome p {font-size:13px;margin-bottom:19px}
    .player-art {display:none}
    .player-primary {min-height:42px;font-size:13px}
    .player-stats {gap:9px;margin:13px 0}
    .player-stat {min-height:78px;padding:11px;border-radius:11px}
    .stat-icon {flex-basis:35px;width:35px;font-size:25px}
    .stat-copy strong {font-size:21px}
    .stat-copy>span {font-size:11px;white-space:normal}
    .player-columns {gap:13px}
    .player-feed {padding:16px 12px 13px;border-radius:13px}
    .player-section-heading {gap:9px}
    .player-dashboard h2 {font-size:19px}
    .player-section-heading p {font-size:12px}
    .player-section-heading>a {font-size:12px}
    .player-empty {padding:16px 12px;min-height:150px}
    .player-sidebar {grid-template-columns:1fr;gap:13px}
    .player-lottery,.player-account {padding:18px}
    .dashboard-footer-separator {margin:0 5px}
}
@media(max-width:365px) {
    .player-welcome {padding:22px 16px}
    .player-stat {gap:6px;padding:10px 7px}
    .stat-icon {flex-basis:29px;width:29px;font-size:22px}
    .stat-copy strong {font-size:19px}
    .stat-copy>span {font-size:10px}
    .player-section-heading p {max-width:195px}
    .player-empty p {font-size:12px}
}
@media(prefers-reduced-motion:reduce) {
    .player-primary,.player-stat,.player-game,.player-secondary {transition:none}
    .player-primary:hover,.player-stat:hover,.player-game:hover {transform:none}
}
</style>
