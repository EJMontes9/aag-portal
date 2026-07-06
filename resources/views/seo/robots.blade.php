User-agent: *
Allow: /

# Bloquear rutas administrativas e internas
Disallow: /admin/
Disallow: /livewire/
Disallow: /api/

# Bloquear parámetros de búsqueda/filtro (evitar contenido duplicado)
Disallow: /*?*categoria=
Disallow: /*?*estado=

# Crawl delay recomendado
Crawl-delay: 1

# Sitemap
Sitemap: {{ url('/sitemap.xml') }}
