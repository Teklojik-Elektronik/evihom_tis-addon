@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_start'))

<div class="card" style="min-width: 24rem;">
  <div class="card-header text-center">
    CMS Token
  </div>
  <div class="card-body">
    <div class="text-center">
    <pre class="mb-0"><code class="d-block">{{ $widget['cms_token'] ?? 'Error Getting the Token' }}</code></pre>
      <button class="btn btn-sm btn-outline-primary mt-3" onclick="copyCode(this)">
        Copy
      </button>
    </div>
  </div>
</div>

@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_end'))

@push('after_scripts')
<script>
  async function copyCode(btn) {
    // grab the <pre><code> text
    const code = btn.closest('.d-flex').querySelector('code').innerText;
    try {
      await navigator.clipboard.writeText(code);
      btn.innerText = 'Copied!';
      setTimeout(() => btn.innerText = 'Copy', 2000);
    } catch (err) {
      console.error('Copy failed', err);
    }
  }
</script>
@endpush
