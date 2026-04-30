// Parse scan.log and extract position data into positions.json
const fs = require('fs');
const path = require('path');

const logPath = path.join(__dirname, 'scan.log');
const outPath = path.join(__dirname, 'positions.json');

const raw = fs.readFileSync(logPath, 'utf-8');

// Find all EXTRACTED_POSITIONS JSON blocks
const regex = /EXTRACTED_POSITIONS:\s*(\{[\s\S]*?\n\})/g;
const merged = {};
let match;

while ((match = regex.exec(raw)) !== null) {
  try {
    const parsed = JSON.parse(match[1]);
    Object.assign(merged, parsed);
  } catch (e) {
    console.error('Failed to parse block:', e.message);
  }
}

fs.writeFileSync(outPath, JSON.stringify(merged, null, 2), 'utf-8');
console.log(`✅ Wrote ${Object.keys(merged).length} routes to positions.json`);

// Print summary
for (const [route, elements] of Object.entries(merged)) {
  console.log(`  ${route}: ${Object.keys(elements).length} elements`);
}
