# Native Node.js HTTP Server for PDF Generation - Implementation Plan

## Overview
Replace the current complex Browsershot → Remote Chrome connection with a native Node.js HTTP server approach using only built-in Node.js modules. This eliminates connection issues and provides a clean, lightweight, zero-dependency solution.

## Current Problem Analysis
- **Complex Setup**: Laravel container trying to connect to remote Chrome via Browsershot
- **Connection Issues**: Network/protocol issues between containers  
- **Silent Failures**: Remote connection errors are swallowed, falling back to non-existent local Chrome
- **Unnecessary Complexity**: Two separate Puppeteer instances (Laravel + Chrome container)

## New Approach: Native HTTP API Server
**Instead of**: Laravel → Browsershot → Puppeteer → Remote Chrome  
**New approach**: Laravel → HTTP POST → Chrome Container (Native Node.js HTTP Server) → Puppeteer → PDF

---

## Phase 1: Create Native HTTP Server Script

### 1.1 PDF Service with Native HTTP Module
- [ ] Create `docker/chrome/pdf-service.js` using `require('node:http')`
- [ ] Implement POST route handling with native request parsing
- [ ] Parse JSON POST body using stream events (`data` and `end`)
- [ ] Generate PDF using Puppeteer
- [ ] Return PDF as response buffer

### 1.2 Request/Response Handling
- [ ] Handle POST requests to `/generate-pdf` endpoint
- [ ] Parse JSON request body from chunks
- [ ] Validate request data and options
- [ ] Set appropriate response headers for PDF content

### 1.3 Error Handling
- [ ] Proper HTTP status codes (200, 400, 500)
- [ ] JSON parsing error handling
- [ ] Puppeteer error handling
- [ ] Resource cleanup on errors

### 1.4 Health Check Endpoint
- [ ] Add GET `/health` endpoint for monitoring
- [ ] Return JSON status response
- [ ] Enable Docker health checks

---

## Phase 2: Docker Configuration Updates

### 2.1 Update Chrome Service
- [ ] Mount `pdf-service.js` script into container
- [ ] Change command to run the HTTP server script
- [ ] Expose port 3000 for HTTP API
- [ ] Remove remote debugging configuration

### 2.2 Environment Setup
- [ ] Update `.env` files for HTTP API URL
- [ ] Remove remote debugging configurations
- [ ] Add timeout and retry settings
- [ ] Update `.env.example` with new settings

### 2.3 Docker Health Checks
- [ ] Add health check configuration to docker-compose.yml
- [ ] Test health check endpoint accessibility
- [ ] Verify container startup and readiness

---

## Phase 3: Laravel Integration Updates

### 3.1 Update PdfService
- [ ] Replace Browsershot with Laravel HTTP client
- [ ] Send POST requests to Chrome container HTTP API
- [ ] Handle response parsing and error codes
- [ ] Implement proper timeout handling

### 3.2 Configuration Updates
- [ ] Update `config/services.php` for HTTP API configuration
- [ ] Add Chrome service URL and timeout settings
- [ ] Remove remote debugging configuration
- [ ] Update environment variables

### 3.3 Error Handling Improvements
- [ ] Map HTTP status codes to exceptions
- [ ] Add retry logic for temporary failures
- [ ] Proper logging for debugging
- [ ] Remove silent failure fallbacks

---

## Phase 4: Testing & Validation

### 4.1 Unit Testing
- [ ] Update existing PDF service tests
- [ ] Add tests for HTTP API communication
- [ ] Test error handling scenarios
- [ ] Verify PDF generation quality

### 4.2 Integration Testing
- [ ] Test PDF generation with real invoice data
- [ ] Test multiple concurrent PDF requests
- [ ] Verify Chrome container startup and health
- [ ] Test service recovery after failures

### 4.3 Performance Testing
- [ ] Measure PDF generation performance
- [ ] Test memory usage and cleanup
- [ ] Verify no memory leaks in Chrome container
- [ ] Load test with multiple requests

---

## Implementation Details

### Native HTTP Server Script (docker/chrome/pdf-service.js):
```javascript
const http = require('node:http');
const puppeteer = require('puppeteer');

const server = http.createServer(async (req, res) => {
  // Set CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  
  // Handle OPTIONS requests
  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }
  
  // Handle POST requests to /generate-pdf
  if (req.method === 'POST' && req.url === '/generate-pdf') {
    try {
      const chunks = [];
      
      // Collect request body chunks
      req.on('data', (chunk) => {
        chunks.push(chunk);
      });
      
      // Process when all data received
      req.on('end', async () => {
        try {
          // Parse JSON from request body
          const bodyData = Buffer.concat(chunks).toString();
          const { html, options = {} } = JSON.parse(bodyData);
          
          if (!html) {
            res.writeHead(400, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ error: 'HTML content is required' }));
            return;
          }
          
          // Launch Puppeteer
          const browser = await puppeteer.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
          });
          
          const page = await browser.newPage();
          await page.setContent(html, { waitUntil: 'networkidle0' });
          
          // PDF generation options
          const pdfOptions = {
            format: 'A4',
            margin: { top: '10mm', right: '10mm', bottom: '10mm', left: '10mm' },
            printBackground: true,
            ...options
          };
          
          // Generate PDF
          const pdfBuffer = await page.pdf(pdfOptions);
          await browser.close();
          
          // Send PDF response
          res.writeHead(200, {
            'Content-Type': 'application/pdf',
            'Content-Length': pdfBuffer.length
          });
          res.end(pdfBuffer);
          
        } catch (error) {
          console.error('PDF generation error:', error);
          res.writeHead(500, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({ error: 'PDF generation failed' }));
        }
      });
      
    } catch (error) {
      console.error('Request processing error:', error);
      res.writeHead(500, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ error: 'Internal server error' }));
    }
  } else if (req.method === 'GET' && req.url === '/health') {
    // Health check endpoint
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ status: 'healthy' }));
  } else {
    // 404 for other routes
    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Not found' }));
  }
});

server.listen(3000, () => {
  console.log('PDF service listening on port 3000');
});
```

### Updated Docker Compose Configuration:
```yaml
chrome:
  image: 'ghcr.io/zenika/alpine-chrome:with-puppeteer'
  ports:
    - '${FORWARD_CHROME_PORT:-3000}:3000'
  volumes:
    - './docker/chrome/pdf-service.js:/usr/src/app/pdf-service.js'
  command: ['node', '/usr/src/app/pdf-service.js']
  networks:
    - sail
  healthcheck:
    test: ["CMD", "wget", "--quiet", "--tries=1", "--spider", "http://localhost:3000/health"]
    interval: 30s
    timeout: 10s
    retries: 3
```

### Updated Laravel PdfService:
```php
use Illuminate\Support\Facades\Http;

private function generatePdfFromHtml(string $html, string $filename): string
{
    $response = Http::timeout(30)
        ->post(config('services.chrome.url') . '/generate-pdf', [
            'html' => $html,
            'options' => [
                'format' => 'A4',
                'margin' => [
                    'top' => '10mm',
                    'right' => '10mm',
                    'bottom' => '10mm',
                    'left' => '10mm'
                ],
                'printBackground' => true
            ]
        ]);

    if ($response->failed()) {
        $errorMessage = $response->json('error') ?? 'PDF generation failed';
        throw new \Exception("PDF generation failed: {$errorMessage}");
    }

    return $response->body();
}
```

### Configuration Updates:
```php
// config/services.php
'chrome' => [
    'enabled' => env('CHROME_SERVICE_ENABLED', false),
    'url' => env('CHROME_SERVICE_URL', 'http://chrome:3000'),
    'timeout' => env('CHROME_SERVICE_TIMEOUT', 30),
],
```

### Environment Variables:
```env
# Chrome service configuration for PDF generation
FORWARD_CHROME_PORT=3000
CHROME_SERVICE_URL=http://chrome:3000
CHROME_SERVICE_TIMEOUT=30
CHROME_SERVICE_ENABLED=true
```

---

## Migration Steps

### Step 1: Preparation
- [ ] Create `docker/chrome/` directory structure
- [ ] Create `pdf-service.js` with native HTTP server
- [ ] Update Docker Compose configuration
- [ ] Update environment files

### Step 2: Chrome Service Setup
- [ ] Test Chrome container with new HTTP server
- [ ] Verify HTTP API endpoints respond correctly
- [ ] Test basic PDF generation via HTTP
- [ ] Validate health check endpoint

### Step 3: Laravel Integration
- [ ] Update PdfService implementation
- [ ] Update configuration files
- [ ] Test PDF generation from Laravel
- [ ] Verify error handling and logging

### Step 4: Testing & Deployment
- [ ] Run comprehensive tests
- [ ] Test with real invoice data
- [ ] Performance testing with load
- [ ] Deploy to environment

### Step 5: Cleanup
- [ ] Remove Browsershot dependency (if desired)
- [ ] Clean up old remote debugging configuration
- [ ] Update documentation
- [ ] Remove unused files and configuration

---

## Expected Outcomes

### Benefits:
- **Zero Dependencies**: Uses only Node.js built-in modules
- **Eliminates Connection Issues**: No remote debugging or WebSocket connections
- **Lightweight**: Native HTTP server without Express overhead
- **Better Error Handling**: Clear HTTP status codes and error messages
- **Performance**: Direct stream processing, no middleware overhead
- **Container Unchanged**: Uses alpine-chrome exactly as designed
- **Production Ready**: Native HTTP module is battle-tested

### Success Criteria:
- [ ] PDF generation works reliably via HTTP API
- [ ] No external dependencies beyond Node.js built-ins
- [ ] Clear error messages and proper HTTP status codes
- [ ] Health check endpoint responds correctly
- [ ] Performance equal or better than current approach
- [ ] Container uses alpine-chrome without modifications
- [ ] All existing tests pass with new implementation

---

## Current Status:
- **Phase 1**: ❌ Not started - Create Native HTTP Server Script
- **Phase 2**: ❌ Not started - Docker Configuration Updates  
- **Phase 3**: ❌ Not started - Laravel Integration Updates
- **Phase 4**: ❌ Not started - Testing & Validation

## Next Steps:
1. **Phase 1.1**: Create the native HTTP server script using Node.js built-in modules
2. **Phase 2.1**: Update Docker Compose configuration to run the HTTP server
3. **Test Basic Setup**: Verify HTTP server responds and generates PDFs
4. **Phase 3.1**: Update Laravel PdfService to use HTTP client
5. **Comprehensive Testing**: Validate complete end-to-end functionality

---

## Implementation Progress Log:
*This section will be updated as work progresses*

- **Started**: [Date to be filled]
- **Phase 1 Complete**: [Date to be filled]  
- **Phase 2 Complete**: [Date to be filled]
- **Phase 3 Complete**: [Date to be filled]
- **Phase 4 Complete**: [Date to be filled]
- **Deployed**: [Date to be filled]