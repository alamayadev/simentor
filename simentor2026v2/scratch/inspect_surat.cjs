const fs = require('fs');
const content = fs.readFileSync('e:/GitHub/Simentor_v1.5/simentor2026v2/src/views/UmumSurat.tsx', 'utf8');
const lines = content.split('\n');

for (let i = 1280; i <= 1290; i++) {
  const line = lines[i - 1];
  console.log(`${i}: ${line}`);
  if (line) {
    let codes = '';
    for (let j = 0; j < line.length; j++) {
      codes += line.charCodeAt(j) + ' ';
    }
    console.log(`   Codes: ${codes}`);
  }
}
