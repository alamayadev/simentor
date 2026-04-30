const fs = require('fs');
const content = fs.readFileSync('e:/GitHub/Simentor_v1.5/simentor2026v2/src/views/RkkDipaPerencanaan.tsx', 'utf8');

let curly = 0;
let paren = 0;
let bracket = 0;

for (let i = 0; i < content.length; i++) {
  const c = content[i];
  if (c === '{') curly++;
  else if (c === '}') curly--;
  else if (c === '(') paren++;
  else if (c === ')') paren--;
  else if (c === '[') bracket++;
  else if (c === ']') bracket--;
  
  if (curly < 0 || paren < 0 || bracket < 0) {
    console.log(`Mismatch at index ${i} (line ${content.slice(0, i).split('\n').length}): ${c}`);
  }
}

console.log(`Final counts: curly=${curly}, paren=${paren}, bracket=${bracket}`);
