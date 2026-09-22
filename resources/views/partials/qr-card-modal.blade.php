<!-- Modal Cetak QR / Kartu Absensi -->
<div id="qrModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <!-- Backdrop with blur effect -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeQrModal()"></div>
    
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="relative bg-white/95 dark:bg-slate-900/95 backdrop-blur-md rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 w-full max-w-md border border-gray-100 dark:border-slate-800">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 px-6 py-4 flex justify-between items-center border-b border-white/10">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-qrcode text-indigo-400"></i> Kartu Akses & QR Code
                </h3>
                <button onclick="closeQrModal()" class="text-slate-400 hover:text-white transition-colors duration-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Tabs Navigation -->
            <div class="flex border-b border-gray-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                <button type="button" onclick="switchQrTab('qr-only')" id="tab-qr-only" class="flex-1 py-3 text-sm font-bold text-indigo-600 border-b-2 border-indigo-600 transition-all text-center focus:outline-none">
                    <i class="fas fa-qrcode mr-1.5"></i> Hanya QR Code
                </button>
                <button type="button" onclick="switchQrTab('card-design')" id="tab-card-design" class="flex-1 py-3 text-sm font-medium text-gray-500 hover:text-slate-800 border-b-2 border-transparent transition-all text-center focus:outline-none">
                    <i class="fas fa-id-card mr-1.5"></i> Desain Kartu Pintar
                </button>
            </div>
            
            <!-- Tab 1: QR Only (Clean QR Code for download/copy) -->
            <div id="tab-content-qr-only" class="p-8 flex flex-col items-center">
                <div class="p-4 bg-white rounded-2xl shadow-lg border border-slate-100 dark:border-slate-800 mb-5 flex items-center justify-center">
                    <img id="qr_large_img" src="" class="w-44 h-44 cursor-pointer" alt="QR Code Large" title="Klik kanan untuk Salin / Simpan">
                </div>
                <div class="text-center mb-6">
                    <h4 id="qr_only_name" class="font-extrabold text-base text-slate-800 dark:text-white uppercase tracking-wide">NAMA LENGKAP</h4>
                    <p id="qr_only_id" class="text-xs font-mono text-slate-500 mt-1">ID: -</p>
                    <p class="text-[10px] text-slate-400 italic mt-2">Dapat didownload, disalin (copy-paste), atau klik kanan pada gambar.</p>
                </div>
                <div class="flex gap-3 w-full">
                    <button type="button" onclick="triggerDownloadQr()" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 bg-indigo-600 text-white text-xs font-bold shadow-md hover:bg-indigo-700 active:scale-95 transition-all">
                        <i class="fas fa-download"></i> Unduh PNG
                    </button>
                    <button type="button" onclick="triggerCopyQr()" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-750 active:scale-95 transition-all">
                        <i class="fas fa-copy"></i> Salin Gambar
                    </button>
                </div>
            </div>
            
            <!-- Tab 2: Card Design (Premium Glassmorphism Preview) -->
            <div id="tab-content-card-design" class="p-8 flex flex-col items-center bg-slate-50/50 dark:bg-slate-950/20 hidden">
                <!-- Smart Card (Premium Official Light Theme) -->
                <div id="cardToPrint" class="mx-auto rounded-xl bg-white text-slate-800 shadow-2xl relative overflow-hidden flex flex-col tracking-wide font-sans select-none print-scale" style="width: 250px; height: 390px; border: 2px solid #e2e8f0;">
                    
                    <!-- Header Section (Navy Blue Block) -->
                    <div id="card_header" class="w-full px-3 py-3 flex flex-col items-center justify-center z-10 relative shrink-0" style="background: linear-gradient(to bottom, #1e3a8a, #1e1b4b); border-bottom: 4px solid #facc15;">
                        <img src="/images/logo-pembda.png" class="object-contain mb-1 drop-shadow-md" style="width: 36px; height: 36px;" alt="Logo">
                        <h4 id="qr_school_name" class="font-extrabold uppercase tracking-widest text-white leading-tight text-center drop-shadow-md" style="font-size: 11px;">YAYASAN PERGURUAN PEMBDA</h4>
                        <p id="qr_card_type" class="text-yellow-300 font-bold tracking-widest uppercase mt-0.5 text-center" style="font-size: 7px;">KARTU IDENTITAS ELEKTRONIK</p>
                    </div>
                    
                    <!-- Middle Section: Photo & Profile Details -->
                    <div class="flex flex-col items-center z-10 w-full flex-1 justify-start pt-3 pb-2 px-3 relative">
                        <!-- Profile Photo Frame (Elegant) -->
                        <div class="rounded-lg overflow-hidden relative mb-2 bg-slate-100" style="width: 85px; height: 105px; border: 3px solid white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); outline: 1px solid #cbd5e1;">
                            <img id="qr_photo" src="/images/default-student.jpg" class="object-cover" style="width: 100%; height: 100%;" alt="Foto">
                            <div id="qr_badge_bg" class="absolute inset-x-0 bottom-0 py-0.5 text-center" style="background-color: rgba(30, 58, 138, 0.9); backdrop-filter: blur(2px); border-top: 1px solid rgba(255,255,255,0.2);">
                                <span id="qr_badge_role" class="font-extrabold uppercase tracking-widest text-white" style="font-size: 8px;">ROLE</span>
                            </div>
                        </div>
                        
                        <!-- Details -->
                        <div class="text-center w-full">
                            <h5 id="qr_name" class="font-extrabold tracking-wide uppercase leading-tight mb-1" style="font-size: 12px; color: #172554; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">NAMA LENGKAP</h5>
                            <div class="mx-auto mb-1.5" style="width: 40px; height: 2px; background-color: #facc15;"></div>
                            
                            <div class="flex flex-col gap-0.5">
                                <p class="font-semibold text-slate-700" style="font-size: 9px;">Kelas/Jabatan: <span id="qr_role_class" class="font-bold" style="color: #1e3a8a;">XI - Budi Utomo</span></p>
                                <p class="font-semibold text-slate-700" style="font-size: 9px;"><span id="qr_nis_label">NIS/NISN</span>: <span id="qr_nis_value" class="font-bold" style="color: #1e3a8a;">7272</span></p>
                                <p class="font-semibold mt-0.5" style="font-size: 8px; color: #475569;">TTL: <span id="qr_ttl_label" class="font-bold uppercase" style="color: #1e293b;">HILIHAO, 28-11-2009</span></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Footer: QR Code ONLY -->
                    <div class="flex flex-col items-center justify-center w-full z-10 mt-auto pb-2">
                        <div class="p-1.5 bg-white border border-slate-200 rounded-lg shadow-sm flex items-center justify-center mb-1">
                            <img id="qr_img" src="" style="width: 75px; height: 75px;" alt="QR Code">
                        </div>
                        <span class="font-bold uppercase tracking-widest" style="font-size: 7px; color: #475569;">Scan Barcode/QR Untuk Verifikasi</span>
                    </div>
                    
                    <!-- Bottom Deco Strip -->
                    <div id="card_bottom_strip" class="absolute bottom-0 inset-x-0 z-20" style="height: 6px; background-color: #1e3a8a;"></div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div id="qr-modal-footer" class="bg-slate-50 dark:bg-slate-900/60 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100 dark:border-slate-800 hidden flex-wrap">
                <button type="button" onclick="printCard()" class="w-full sm:flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 bg-gradient-to-r from-yellow-400 to-amber-500 text-sm font-extrabold text-slate-900 shadow-lg shadow-yellow-500/20 hover:from-yellow-500 hover:to-amber-600 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <i class="fas fa-print"></i> Cetak Kartu
                </button>
                <button type="button" onclick="downloadCardAsImage()" class="w-full sm:flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-600 hover:to-teal-700 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <i class="fas fa-download"></i> Unduh PNG
                </button>
                <button type="button" onclick="closeQrModal()" class="w-full sm:flex-1 inline-flex items-center justify-center rounded-xl border border-gray-300 dark:border-slate-700 px-6 py-3 bg-white dark:bg-slate-800 text-sm font-semibold text-gray-700 dark:text-slate-300 shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 active:scale-95 transition-all duration-200">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentQrCode = '';
    let currentQrName = '';

    function openQrModal(name, code, photo, roleClass, schoolName, role, ttl = '-') {
        document.getElementById('qrModal').classList.remove('hidden');
        
        currentQrCode = code;
        currentQrName = name;

        // Populate Tab 1: QR Only
        document.getElementById('qr_only_name').innerText = name;
        document.getElementById('qr_only_id').innerText = (role === 'Siswa' ? 'NIS/NISN: ' : 'NIP/Kode: ') + code;
        const qrLarge = document.getElementById('qr_large_img');
        qrLarge.crossOrigin = "anonymous";
        qrLarge.src = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${code}&_t=${new Date().getTime()}`;
        
        // Populate Tab 2: Card Design
        document.getElementById('qr_name').innerText = name;
        document.getElementById('qr_nis_label').innerText = (role === 'Siswa' ? 'NIS/NISN' : 'NIP/Kode');
        document.getElementById('qr_nis_value').innerText = code;
        document.getElementById('qr_ttl_label').innerText = ttl;
        
        const qrPhoto = document.getElementById('qr_photo');
        qrPhoto.crossOrigin = "anonymous";
        const photoSrc = photo || '/images/default-student.jpg';
        qrPhoto.src = photoSrc + (photoSrc.includes('?') ? '&' : '?') + '_t=' + new Date().getTime();
        
        document.getElementById('qr_role_class').innerText = roleClass;
        document.getElementById('qr_badge_role').innerText = role;
        document.getElementById('qr_school_name').innerText = schoolName || 'YAYASAN PERGURUAN PEMBDA';
        
        const qrImg = document.getElementById('qr_img');
        qrImg.crossOrigin = "anonymous";
        qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${code}&_t=${new Date().getTime()}`;
        
        // Simpan raw role untuk keperluan kanvas
        document.getElementById('cardToPrint').setAttribute('data-role', role);

        // Reset to default tab (QR Only)
        switchQrTab('qr-only');
        
        // Dynamic Design adaptation based on role
        const cardType = document.getElementById('qr_card_type');
        const badgeBg = document.getElementById('qr_badge_bg');
        const header = document.getElementById('card_header');
        const bottomStrip = document.getElementById('card_bottom_strip');
        
        if (role === 'Siswa') {
            cardType.innerText = 'KARTU IDENTITAS SISWA';
            badgeBg.style.backgroundColor = 'rgba(30, 58, 138, 0.9)'; // blue-900
            header.style.background = 'linear-gradient(to bottom, #1e3a8a, #1e1b4b)'; // blue-900 to indigo-950
            bottomStrip.style.backgroundColor = '#1e3a8a';
        } else if (role === 'Guru') {
            cardType.innerText = 'KARTU IDENTITAS GURU';
            badgeBg.style.backgroundColor = 'rgba(4, 120, 87, 0.9)'; // emerald-700
            header.style.background = 'linear-gradient(to bottom, #065f46, #134e4a)'; // emerald-800 to teal-900
            bottomStrip.style.backgroundColor = '#065f46';
        } else {
            cardType.innerText = 'KARTU IDENTITAS PEGAWAI';
            badgeBg.style.backgroundColor = 'rgba(126, 34, 206, 0.9)'; // purple-700
            header.style.background = 'linear-gradient(to bottom, #6b21a8, #312e81)'; // purple-800 to indigo-900
            bottomStrip.style.backgroundColor = '#6b21a8';
        }
    }

    function downloadCardAsImage() {
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        btn.disabled = true;

        // Native Canvas Drawing for 100% Reliable Render (Bypassing CSS/SVG bugs)
        const scale = 4; // High Res 4x
        const width = 250 * scale;
        const height = 390 * scale;

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');

        // Polyfill for roundRect
        if (!CanvasRenderingContext2D.prototype.roundRect) {
            CanvasRenderingContext2D.prototype.roundRect = function (x, y, w, h, r) {
                if (w < 2 * r) r = w / 2;
                if (h < 2 * r) r = h / 2;
                this.beginPath();
                this.moveTo(x+r, y);
                this.arcTo(x+w, y, x+w, y+h, r);
                this.arcTo(x+w, y+h, x, y+h, r);
                this.arcTo(x, y+h, x, y, r);
                this.arcTo(x, y, x+w, y, r);
                this.closePath();
                return this;
            }
        }

        const loadImage = (src) => {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => resolve(img);
                img.onerror = () => reject(new Error('Failed to load image: ' + src));
                img.src = src;
            });
        };

        const name = document.getElementById('qr_name').innerText;
        const code = document.getElementById('qr_nis_value').innerText;
        const ttl = document.getElementById('qr_ttl_label').innerText;
        const roleClass = document.getElementById('qr_role_class').innerText;
        const role = document.getElementById('cardToPrint').getAttribute('data-role'); // Gunakan raw role
        const cardType = document.getElementById('qr_card_type').innerText;

        let photoSrc = document.getElementById('qr_photo').src;
        let qrSrc = document.getElementById('qr_img').src;
        let logoSrc = '/images/logo-pembda.png?_t=' + new Date().getTime();

        Promise.all([
            loadImage(logoSrc),
            loadImage(photoSrc),
            loadImage(qrSrc)
        ]).then(([logoImg, photoImg, qrImg]) => {
            // 1. Base White Background
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, width, height);
            
            // 2. Header & Colors
            let headerColor = '#1e3a8a';
            let badgeColor = 'rgba(30, 58, 138, 0.9)';
            if (role === 'Guru') {
                headerColor = '#065f46';
                badgeColor = 'rgba(4, 120, 87, 0.9)';
            } else if (role !== 'Siswa') {
                headerColor = '#6b21a8';
                badgeColor = 'rgba(126, 34, 206, 0.9)';
            }
            
            ctx.fillStyle = headerColor;
            ctx.fillRect(0, 0, width, 70 * scale);
            
            ctx.fillStyle = '#facc15';
            ctx.fillRect(0, 70 * scale, width, 4 * scale);

            // Bottom strip
            ctx.fillStyle = headerColor;
            ctx.fillRect(0, height - (6 * scale), width, 6 * scale);

            // 3. Header Text & Logo
            ctx.drawImage(logoImg, width/2 - (18 * scale), 12 * scale, 36 * scale, 36 * scale);
            
            ctx.textAlign = 'center';
            ctx.fillStyle = '#ffffff';
            ctx.font = '900 ' + (11 * scale) + 'px sans-serif';
            ctx.fillText('YAYASAN PERGURUAN PEMBDA', width/2, 58 * scale);
            
            ctx.fillStyle = '#fde047';
            ctx.font = 'bold ' + (7 * scale) + 'px sans-serif';
            ctx.fillText(cardType, width/2, 67 * scale);

            // 4. Profile Photo
            const photoW = 85 * scale;
            const photoH = 105 * scale;
            const photoX = width/2 - photoW/2;
            const photoY = 85 * scale;

            ctx.save();
            ctx.beginPath();
            ctx.roundRect(photoX, photoY, photoW, photoH, 8 * scale);
            ctx.fillStyle = '#f1f5f9';
            ctx.fill();
            ctx.lineWidth = 3 * scale;
            ctx.strokeStyle = '#ffffff';
            ctx.stroke();
            ctx.clip();
            
            // Object-fit cover logic (Gunakan naturalWidth agar aman di semua browser)
            const nWidth = photoImg.naturalWidth || photoImg.width || 1;
            const nHeight = photoImg.naturalHeight || photoImg.height || 1;
            const imgRatio = nWidth / nHeight;
            const boxRatio = photoW / photoH;
            let drawW = photoW, drawH = photoH, drawX = photoX, drawY = photoY;
            
            if (imgRatio > boxRatio) {
                drawW = photoH * imgRatio;
                drawX = photoX - (drawW - photoW) / 2;
            } else {
                drawH = photoW / imgRatio;
                drawY = photoY - (drawH - photoH) / 2;
            }
            ctx.drawImage(photoImg, drawX, drawY, drawW, drawH);
            
            // Badge Role
            ctx.fillStyle = badgeColor;
            ctx.fillRect(photoX, photoY + photoH - (12 * scale), photoW, 12 * scale);
            ctx.fillStyle = '#ffffff';
            ctx.font = '900 ' + (8 * scale) + 'px sans-serif';
            ctx.fillText(role.toUpperCase(), width/2, photoY + photoH - (3 * scale));
            ctx.restore();
            
            // Outer Border
            ctx.beginPath();
            ctx.roundRect(photoX, photoY, photoW, photoH, 8 * scale);
            ctx.lineWidth = 1 * scale;
            ctx.strokeStyle = '#cbd5e1';
            ctx.stroke();

            // 5. Details
            let textY = 215 * scale;
            ctx.fillStyle = '#172554';
            ctx.font = '900 ' + (13 * scale) + 'px sans-serif';
            
            const words = name.toUpperCase().split(' ');
            let line = '';
            let lines = [];
            for(let n = 0; n < words.length; n++) {
                let testLine = line + words[n] + ' ';
                let metrics = ctx.measureText(testLine);
                if (metrics.width > width - (40 * scale) && n > 0) {
                    lines.push(line);
                    line = words[n] + ' ';
                } else {
                    line = testLine;
                }
            }
            lines.push(line);
            if (lines.length > 2) lines = lines.slice(0, 2);
            
            lines.forEach(l => {
                ctx.fillText(l.trim(), width/2, textY);
                textY += 15 * scale;
            });

            // Yellow line
            textY += 2 * scale;
            ctx.fillStyle = '#facc15';
            ctx.fillRect(width/2 - (20 * scale), textY - (1 * scale), 40 * scale, 2 * scale);

            // Detailed labels
            const drawLabeledText = (label, value, y) => {
                ctx.font = '600 ' + (9 * scale) + 'px sans-serif';
                const labelW = ctx.measureText(label).width;
                ctx.font = 'bold ' + (9 * scale) + 'px sans-serif';
                const valueW = ctx.measureText(value).width;
                const totalW = labelW + valueW;
                const startX = width/2 - totalW/2;
                
                ctx.fillStyle = '#334155';
                ctx.font = '600 ' + (9 * scale) + 'px sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(label, startX, y);
                
                ctx.fillStyle = '#1e3a8a';
                ctx.font = 'bold ' + (9 * scale) + 'px sans-serif';
                ctx.fillText(value, startX + labelW, y);
                ctx.textAlign = 'center';
            };

            textY += 16 * scale;
            drawLabeledText('Kelas/Jabatan: ', roleClass, textY);
            
            textY += 14 * scale;
            const nisLabel = role === 'Siswa' ? 'NIS/NISN: ' : 'NIP/Kode: ';
            drawLabeledText(nisLabel, code, textY);
            
            textY += 14 * scale;
            ctx.font = '600 ' + (8 * scale) + 'px sans-serif';
            const ttlLabelW = ctx.measureText('TTL: ').width;
            ctx.font = 'bold ' + (8 * scale) + 'px sans-serif';
            const ttlValW = ctx.measureText(ttl).width;
            const ttlTotalW = ttlLabelW + ttlValW;
            ctx.textAlign = 'left';
            ctx.fillStyle = '#475569';
            ctx.font = '600 ' + (8 * scale) + 'px sans-serif';
            ctx.fillText('TTL: ', width/2 - ttlTotalW/2, textY);
            ctx.fillStyle = '#1e293b';
            ctx.font = 'bold ' + (8 * scale) + 'px sans-serif';
            ctx.fillText(ttl.toUpperCase(), width/2 - ttlTotalW/2 + ttlLabelW, textY);
            ctx.textAlign = 'center';

            // 6. QR Code (Enlarged Barcode/QR)
            const qrSize = 75 * scale;
            const qrY = 292 * scale;
            
            ctx.fillStyle = '#ffffff';
            ctx.shadowColor = 'rgba(0,0,0,0.1)';
            ctx.shadowBlur = 4 * scale;
            ctx.beginPath();
            ctx.roundRect(width/2 - qrSize/2 - (2*scale), qrY - (2*scale), qrSize + (4*scale), qrSize + (4*scale), 4 * scale);
            ctx.fill();
            ctx.shadowColor = 'transparent';
            
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 1 * scale;
            ctx.beginPath();
            ctx.roundRect(width/2 - qrSize/2 - (2*scale), qrY - (2*scale), qrSize + (4*scale), qrSize + (4*scale), 4 * scale);
            ctx.stroke();

            ctx.drawImage(qrImg, width/2 - qrSize/2, qrY, qrSize, qrSize);

            ctx.fillStyle = '#64748b';
            ctx.font = 'bold ' + (6 * scale) + 'px sans-serif';
            ctx.fillText('SCAN QR UNTUK VERIFIKASI', width/2, qrY + qrSize + (10 * scale));

            // Export
            const dataUrl = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = `ID-Card-${name.replace(/\s+/g, '-')}.png`;
            link.href = dataUrl;
            link.click();
            
            btn.innerHTML = originalText;
            btn.disabled = false;

        }).catch(err => {
            console.error('Canvas Draw Error:', err);
            alert('Gagal membuat gambar PNG. Pastikan koneksi stabil. Error: ' + err.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    function switchQrTab(tab) {
        const tabQrOnly = document.getElementById('tab-qr-only');
        const tabCardDesign = document.getElementById('tab-card-design');
        const contentQrOnly = document.getElementById('tab-content-qr-only');
        const contentCardDesign = document.getElementById('tab-content-card-design');
        const modalFooter = document.getElementById('qr-modal-footer');
        
        if (tab === 'qr-only') {
            tabQrOnly.className = "flex-1 py-3 text-sm font-bold text-indigo-600 border-b-2 border-indigo-600 transition-all text-center focus:outline-none";
            tabCardDesign.className = "flex-1 py-3 text-sm font-medium text-gray-500 hover:text-slate-800 border-b-2 border-transparent transition-all text-center focus:outline-none";
            contentQrOnly.classList.remove('hidden');
            contentCardDesign.classList.add('hidden');
            modalFooter.classList.add('hidden');
        } else {
            tabQrOnly.className = "flex-1 py-3 text-sm font-medium text-gray-500 hover:text-slate-800 border-b-2 border-transparent transition-all text-center focus:outline-none";
            tabCardDesign.className = "flex-1 py-3 text-sm font-bold text-indigo-600 border-b-2 border-indigo-600 transition-all text-center focus:outline-none";
            contentQrOnly.classList.add('hidden');
            contentCardDesign.classList.remove('hidden');
            modalFooter.classList.remove('hidden');
        }
    }

    function closeQrModal() {
        document.getElementById('qrModal').classList.add('hidden');
    }

    async function triggerDownloadQr() {
        if (!currentQrCode) return;
        const button = document.querySelector('button[onclick="triggerDownloadQr()"]');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mendownload...';
        button.disabled = true;
        
        try {
            const response = await fetch(`https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${currentQrCode}`);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `QR_${currentQrName.replace(/\s+/g, '_')}_${currentQrCode}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Error downloading QR:', error);
            window.open(`https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${currentQrCode}`, '_blank');
        } finally {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }

    async function triggerCopyQr() {
        if (!currentQrCode) return;
        const button = document.querySelector('button[onclick="triggerCopyQr()"]');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyalin...';
        button.disabled = true;

        try {
            const response = await fetch(`https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${currentQrCode}`);
            const blob = await response.blob();
            await navigator.clipboard.write([
                new ClipboardItem({
                    [blob.type]: blob
                })
            ]);
            alert('Gambar QR Code berhasil disalin ke clipboard! Silakan paste (Ctrl+V) di Microsoft Word, Canva, atau editor lainnya.');
        } catch (error) {
            console.error('Error copying QR:', error);
            alert('Gagal menyalin otomatis. Silakan klik kanan pada QR Code lalu pilih "Salin Gambar" / "Copy Image".');
        } finally {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }

    function printCard() {
        const cardHtml = document.getElementById('cardToPrint').outerHTML;
        const role = document.getElementById('qr_badge_role').innerText;
        
        let roleThemeColor = '#6366f1';
        if (role === 'Guru') {
            roleThemeColor = '#10b981';
        } else if (role === 'Pegawai') {
            roleThemeColor = '#3b82f6';
        }

        const win = window.open('', '_blank');
        const tailwindScript = '<script src="https://cdn.tailwindcss.com"></' + 'script>';
        const scriptOpen = '<script>';
        const scriptClose = '</' + 'script>';

        win.document.write(`
            <html>
            <head>
                <title>Cetak Kartu Absensi - ${role}</title>
                ${tailwindScript}
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    body {
                        margin: 0;
                        padding: 0;
                        background-color: #f8fafc;
                        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    }
                    @media print {
                        * {
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                            color-adjust: exact !important;
                        }
                        body {
                            background-color: transparent;
                        }
                        .no-print {
                            display: none !important;
                        }
                        .print-container {
                            padding: 0;
                            margin: 0;
                            min-height: auto;
                            display: block;
                        }
                        .print-scale {
                            transform: none !important;
                            box-shadow: none !important;
                            border: 1px solid #cbd5e1 !important;
                        }
                    }
                </style>
            </head>
            <body class="flex flex-col items-center justify-center min-h-screen">
                <div class="no-print mb-8 p-4 bg-white shadow-md rounded-2xl flex items-center gap-4 w-full max-w-md border border-slate-100">
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-slate-800">Cetak Kartu Absensi</h3>
                        <p class="text-xs text-slate-500">Format kartu berukuran pas untuk ID Card/Dompet.</p>
                    </div>
                    <button onclick="window.print()" class="px-5 py-2.5 bg-slate-900 text-white font-bold text-xs rounded-xl shadow-lg hover:bg-slate-800 transition-all flex items-center gap-2">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
                
                <div class="print-container flex items-center justify-center">
                    <div class="print-scale transform scale-125 transition-all duration-300">
                        ${cardHtml}
                    </div>
                </div>
                
                ${scriptOpen}
                    window.onload = function() {
                        setTimeout(() => {
                            window.print();
                        }, 600);
                    }
                ${scriptClose}
            </body>
            </html>
        `);
        win.document.close();
    }
</script>
@endpush
