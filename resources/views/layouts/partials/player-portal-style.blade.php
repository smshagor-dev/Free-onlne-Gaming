<style>
/* Same sidebar, topbar, and mobile drawer primitives as the administrator.
   The player portal keeps the dark content area used by the games interface. */
body.fg-page.cms-body.user-portal {
    min-width:0;
    min-height:100vh;
    margin:0;
    background:#0b1423;
    color:#e8eef7;
}
.fg-page.user-portal .cms-shell {width:100%;min-width:0}
.fg-page.user-portal .cms-stage {
    display:flex;
    flex-direction:column;
    min-height:100vh;
    min-width:0;
    background:#0b1423;
}
.fg-page.user-portal .cms-topbar {
    width:100%;
    min-width:0;
    background:#101f32;
    border-bottom:1px solid #29405c;
    color:#f1f7ff;
}
.fg-page.user-portal .cms-top-title strong {color:#f2f8ff}
.fg-page.user-portal .cms-top-title small {color:#a7bfd8}
.fg-page.user-portal .cms-website-link {
    background:#182b42;
    border-color:#35516d;
    color:#d7edff!important;
}
.fg-page.user-portal .cms-top-user,
.fg-page.user-portal .cms-top-user strong {color:#f0f7ff}
.fg-page.user-portal .cms-top-user small {color:#91acc9}
.fg-page.user-portal .cms-menu-toggle {
    background:#14283e;
    border-color:#365573;
    color:#eff7ff;
}
.fg-page.user-portal .cms-content.site-main {
    flex:1;
    width:100%;
    min-width:0;
    max-width:none;
    margin:0;
    padding:clamp(18px,2.5vw,36px);
    background:transparent;
}
.fg-page.user-portal.player-dashboard-route .cms-content.site-main {
    max-width:none;
    margin:0;
    padding:clamp(18px,2.5vw,36px);
}
.fg-page.user-portal .cms-footer {
    border-top:1px solid #253850;
    color:#809ab7;
}
.fg-page.user-portal .cms-footer a {color:#a7d6ff!important}
.fg-page.user-portal .cms-footer a:hover {color:#e1f4ff!important}
.fg-page.user-portal .cms-content .account-nav {display:none!important}
.fg-page.user-portal a:focus-visible,
.fg-page.user-portal button:focus-visible {outline:2px solid #71ccff;outline-offset:3px}
@media(max-width:900px) {
    .fg-page.user-portal .cms-topbar {padding:13px 16px}
    .fg-page.user-portal .cms-content.site-main {padding:19px 16px}
    .fg-page.user-portal.player-dashboard-route .cms-content.site-main {padding:19px 16px}
    .fg-page.user-portal .cms-sidebar {box-shadow:10px 0 36px #0008}
}
@media(max-width:580px) {
    .fg-page.user-portal .cms-content.site-main,
    .fg-page.user-portal.player-dashboard-route .cms-content.site-main {padding:12px 11px 20px}
    .fg-page.user-portal .cms-top-title strong {font-size:14px}
    .fg-page.user-portal .cms-top-title small {display:none}
    .fg-page.user-portal .cms-top-actions {gap:6px}
    .fg-page.user-portal .cms-top-user {min-width:0}
    .fg-page.user-portal .cms-website-link {padding:7px 8px}
    .fg-page.user-portal .cms-footer {padding:18px 11px}
}
</style>
