#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('🔨 Building CNF WordPress Schema...\n');

// Check if tsx is available, if not install it temporarily
let useTsx = false;
try {
  execSync('npx tsx --version', { stdio: 'ignore' });
  useTsx = true;
  console.log('✅ Found tsx');
} catch {
  console.log('📦 Installing tsx temporarily...');
  execSync('npm install -D tsx', { stdio: 'inherit' });
  useTsx = true;
}

// Compile TypeScript to JSON using tsx
try {
  console.log('🔄 Compiling wp-schema.ts...\n');

  // Create a temporary script that imports and exports the schema as JSON
  const tempScript = `
    import schema from './wp-schema.ts';
    console.log(JSON.stringify(schema, null, 2));
  `;

  const tempFile = path.join(__dirname, '.temp-build.ts');
  fs.writeFileSync(tempFile, tempScript);

  // Execute with tsx and capture output
  const output = execSync('npx tsx .temp-build.ts', {
    encoding: 'utf8',
    maxBuffer: 50 * 1024 * 1024 // 50MB buffer for large schema
  });

  // Clean up temp file
  fs.unlinkSync(tempFile);

  // Write the schema JSON
  const outputPath = path.join(__dirname, 'wp-content/mu-plugins/cnf-setup/schema.json');
  const outputDir = path.dirname(outputPath);

  // Ensure directory exists
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }

  fs.writeFileSync(outputPath, output);

  console.log('✅ Schema compiled successfully!');
  console.log(`📄 Output: ${outputPath}`);

  // Show file size
  const stats = fs.statSync(outputPath);
  console.log(`📊 Size: ${(stats.size / 1024).toFixed(2)} KB\n`);

} catch (error) {
  console.error('❌ Build failed:', error.message);
  process.exit(1);
}
