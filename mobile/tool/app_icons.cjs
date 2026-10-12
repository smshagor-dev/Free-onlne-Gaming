const fs = require('fs');
const path = require('path');
const {chromium} = require('playwright');
(async () => {
  const root = path.resolve(__dirname, '..');
  const browser = await chromium.launch({executablePath: process.env.CHROMIUM_PATH || chromium.executablePath(), args: ['--no-sandbox']});
  const page = await browser.newPage();
  const svg = fs.readFileSync(path.join(root, 'assets/app-icon.svg'), 'utf8');
  const icons = Object.entries({mdpi:48,hdpi:72,xhdpi:96,xxhdpi:144,xxxhdpi:192})
    .map(([density,size]) => [path.join(root, `android/app/src/main/res/mipmap-${density}/ic_launcher.png`),size]);
  const ios = path.join(root,'ios/Runner/Assets.xcassets/AppIcon.appiconset');
  for (const icon of JSON.parse(fs.readFileSync(path.join(ios,'Contents.json'),'utf8')).images) {
    if (icon.filename) icons.push([path.join(ios,icon.filename),Math.round(parseFloat(icon.size)*parseFloat(icon.scale))]);
  }
  for (const [file,size] of icons) {
    await page.setViewportSize({width:size,height:size});
    await page.setContent(`<style>body{margin:0}svg{width:100vw;height:100vh}</style>${svg}`);
    await page.screenshot({path:file});
  }
  await browser.close();
  console.log(`Rendered ${icons.length} launcher icons.`);
})().catch(error => { console.error(error); process.exit(1); });
