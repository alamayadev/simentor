const fs = require('fs');
const content = fs.readFileSync('e:/GitHub/Simentor_v1.5/simentor2026v2/src/views/RkkDipaPerencanaan.tsx', 'utf8');
const lines = content.split('\n');

for (let i = 965; i <= 975; i++) {
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
