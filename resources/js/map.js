import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const mapElement = document.getElementById('tourism-map');

if (mapElement) {
    const isRouteMap = mapElement.dataset.isRouteMap === 'true';
    const map = L.map(mapElement, { zoomControl: true }).setView([9.145, 40.489], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    const markerLayer = L.layerGroup().addTo(map);
    const polylineLayer = L.layerGroup().addTo(map);
    const statusElement = document.getElementById('map-status');
    const loadingElement = document.getElementById('map-loading');
    const countElement = document.getElementById('map-result-count');
    const filters = document.getElementById('map-filters');
    let currentRequest;
    let userLocationMarker;
    const markerMap = new Map();

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
        if (!statusElement) return;
        statusElement.textContent = message;
        statusElement.className = `alert alert-${type}`;
        statusElement.classList.remove('d-none');
    };

    const loadMarkers = async () => {
        if (currentRequest) currentRequest.abort();
        currentRequest = new AbortController();
        markerLayer.clearLayers();
        polylineLayer.clearLayers();
        markerMap.clear();

        if (loadingElement) loadingElement.classList.remove('d-none');
        if (statusElement) statusElement.classList.add('d-none');

        const params = filters ? new URLSearchParams(new FormData(filters)) : new URLSearchParams();
        try {
            const response = await fetch(`${mapElement.dataset.endpoint}?${params}`, {
                headers: { Accept: 'application/json' },
                signal: currentRequest.signal,
            });
            if (!response.ok) throw new Error('Map data request failed');
            const payload = await response.json();
            const markers = payload.data ?? [];
            const segments = payload.route_segments ?? [];

            if (countElement) {
                countElement.textContent = `${markers.length} mapped ${markers.length === 1 ? 'place' : 'places'}`;
            }

            // Draw route polylines if available
            if (segments.length > 0) {
                segments.forEach((seg) => {
                    const latLngs = seg.polyline;
                    const polyline = L.polyline(latLngs, {
                        color: '#0f5132',
                        weight: 4,
                        opacity: 0.85,
                        dashArray: '6, 8',
                    }).addTo(polylineLayer);

                    polyline.bindTooltip(`<strong>${escapeHtml(seg.from_title)} &rarr; ${escapeHtml(seg.to_title)}</strong><br>🚗 ${escapeHtml(seg.formatted_distance)} &bull; ⏱️ ${escapeHtml(seg.formatted_duration)}`, {
                        sticky: true,
                        className: 'route-leg-tooltip',
                    });
                });
            }

            // Render markers
            markers.forEach((marker, index) => {
                const color = colors[marker.type] ?? colors.tourism_service;
                const seq = marker.sequence_number ?? (index + 1);

                let leafletMarker;
                if (isRouteMap) {
                    // Custom numbered marker icon
                    const numberedIcon = L.divIcon({
                        className: 'custom-route-marker',
                        html: `<div style="background-color: ${color}; width: 28px; height: 28px; border-radius: 50%; color: #fff; font-weight: bold; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-size: 12px;">${seq}</div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14],
                    });
                    leafletMarker = L.marker([marker.latitude, marker.longitude], { icon: numberedIcon }).addTo(markerLayer);
                } else {
                    leafletMarker = L.circleMarker([marker.latitude, marker.longitude], {
                        radius: 8,
                        color,
                        fillColor: color,
                        fillOpacity: 0.85,
                        weight: 2,
                    }).addTo(markerLayer);
                }

                const popupHtml = `
                    <div class="map-popup" style="min-width: 180px;">
                        <div class="d-flex align-items-center gap-1 mb-1">
                            <span class="badge" style="background-color: ${color}; color: #fff; font-size: 10px;">${escapeHtml(marker.type.replaceAll('_', ' '))}</span>
                            ${marker.day_number ? `<span class="badge text-bg-light border" style="font-size: 10px;">Day ${marker.day_number}</span>` : ''}
                        </div>
                        <strong class="d-block text-dark">${escapeHtml(marker.title)}</strong>
                        <div class="small text-muted mb-2">📍 ${escapeHtml(marker.summary ?? '')}</div>
                        ${marker.price_hint ? `<div class="small fw-bold text-success mb-2">${escapeHtml(marker.price_hint)}</div>` : ''}
                        ${marker.url ? `<a class="btn btn-sm btn-outline-success w-100 fw-semibold" href="${escapeHtml(marker.url)}" target="_blank">View Details &rarr;</a>` : ''}
                    </div>
                `;
                leafletMarker.bindPopup(popupHtml);
                markerMap.set(`${marker.latitude},${marker.longitude}`, leafletMarker);
            });

            if (markers.length) {
                map.fitBounds(L.latLngBounds(markers.map((m) => [m.latitude, m.longitude])), {
                    padding: [40, 40],
                    maxZoom: 14,
                });
            } else {
                showStatus('No mapped places are available yet.', 'light');
                map.setView([9.145, 40.489], 6);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                showStatus('Map data could not be loaded.', 'warning');
            }
        } finally {
            if (loadingElement) loadingElement.classList.add('d-none');
        }
    };

    if (filters) {
        filters.addEventListener('submit', (event) => {
            event.preventDefault();
            loadMarkers();
        });
    }

    const nearMeBtn = document.getElementById('map-near-me');
    if (nearMeBtn) {
        nearMeBtn.addEventListener('click', () => {
            if (!navigator.geolocation) return showStatus('Geolocation is not available in this browser.', 'info');
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const point = [position.coords.latitude, position.coords.longitude];
                    if (userLocationMarker) userLocationMarker.remove();
                    userLocationMarker = L.circleMarker(point, {
                        radius: 8,
                        color: '#212529',
                        fillColor: '#0dcaf0',
                        fillOpacity: 1,
                    }).addTo(map).bindPopup('Your current location').openPopup();
                    map.setView(point, 12);
                    showStatus('Showing your current location for this session only.', 'info');
                },
                () => showStatus('Location permission was not granted.', 'info')
            );
        });
    }

    // Attach click listeners on waypoint cards in the DOM to pan to marker
    document.querySelectorAll('.waypoint-card').forEach((card) => {
        card.addEventListener('click', () => {
            const lat = parseFloat(card.dataset.lat);
            const lng = parseFloat(card.dataset.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                map.setView([lat, lng], 14, { animate: true });
                const marker = markerMap.get(`${lat},${lng}`);
                if (marker) marker.openPopup();
            }
        });
    });

    loadMarkers();
}
