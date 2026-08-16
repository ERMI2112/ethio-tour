import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const mapElement = document.getElementById('tourism-map');

if (mapElement) {
    const map = L.map(mapElement, { zoomControl: true }).setView([9.145, 40.489], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    const markerLayer = L.layerGroup().addTo(map);
    const statusElement = document.getElementById('map-status');
    const loadingElement = document.getElementById('map-loading');
    const countElement = document.getElementById('map-result-count');
    const filters = document.getElementById('map-filters');
    let currentRequest;
    let userLocationMarker;

    const colors = {
        destination: '#0f5132',
        heritage_site: '#8a5a00',
        museum: '#6f42c1',
        hotel: '#0d6efd',
        restaurant: '#dc3545',
        transportation: '#198754',
        event: '#d63384',
        tourism_service: '#495057',
    };

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    }[character]));

    const showStatus = (message, type = 'info') => {
        statusElement.textContent = message;
        statusElement.className = `alert alert-${type}`;
    };

    const loadMarkers = async () => {
        if (currentRequest) currentRequest.abort();
        currentRequest = new AbortController();
        markerLayer.clearLayers();
        loadingElement.classList.remove('d-none');
        statusElement.classList.add('d-none');

        const params = new URLSearchParams(new FormData(filters));
        try {
            const response = await fetch(`${mapElement.dataset.endpoint}?${params}`, { headers: { Accept: 'application/json' }, signal: currentRequest.signal });
            if (!response.ok) throw new Error('Map data request failed');
            const payload = await response.json();
            const markers = payload.data ?? [];
            countElement.textContent = `${markers.length} mapped ${markers.length === 1 ? 'place' : 'places'}`;

            markers.forEach((marker) => {
                const color = colors[marker.type] ?? colors.tourism_service;
                L.circleMarker([marker.latitude, marker.longitude], { radius: 8, color, fillColor: color, fillOpacity: 0.85, weight: 2 }).bindPopup(`<div class="map-popup"><strong>${escapeHtml(marker.title)}</strong><div class="small text-muted mb-2">${escapeHtml(marker.type.replaceAll('_', ' '))}</div><p class="small mb-2">${escapeHtml(marker.summary ?? '')}</p><a class="btn btn-sm btn-outline-success" href="${escapeHtml(marker.url)}">View details</a></div>`).addTo(markerLayer);
            });

            if (markers.length) {
                map.fitBounds(L.latLngBounds(markers.map((marker) => [marker.latitude, marker.longitude])), { padding: [30, 30], maxZoom: 14 });
            } else {
                showStatus('No mapped places are available yet. Try another filter or check back after locations are verified.', 'light');
                map.setView([9.145, 40.489], 6);
            }
        } catch (error) {
            if (error.name !== 'AbortError') showStatus('Map data could not be loaded. Public discovery pages remain available.', 'warning');
        } finally {
            loadingElement.classList.add('d-none');
        }
    };

    filters.addEventListener('submit', (event) => { event.preventDefault(); loadMarkers(); });
    document.getElementById('map-near-me').addEventListener('click', () => {
        if (!navigator.geolocation) return showStatus('Near me is not available in this browser. You can still explore the map normally.', 'info');
        navigator.geolocation.getCurrentPosition((position) => {
            const point = [position.coords.latitude, position.coords.longitude];
            if (userLocationMarker) userLocationMarker.remove();
            userLocationMarker = L.circleMarker(point, { radius: 8, color: '#212529', fillColor: '#0dcaf0', fillOpacity: 1 }).addTo(map).bindPopup('Your current location').openPopup();
            map.setView(point, 12);
            showStatus('Showing your current location for this session only. It is not stored.', 'info');
        }, () => showStatus('Location permission was not granted. The map continues to work normally.', 'info'));
    });

    loadMarkers();
}
