import './bootstrap';
import * as htmlToImage from 'html-to-image';
import html2canvas from 'html2canvas';

window.htmlToImage = htmlToImage;
window.html2canvas = html2canvas;

window.downloadEcard = async function(elementId, filename = 'E-Card-Ekskul-Pembda.png') {
    const el = document.getElementById(elementId);
    if (!el) {
        alert('Elemen kartu tidak ditemukan.');
        return;
    }
    
    const btn = window.event ? window.event.currentTarget : null;
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Merender HD...</span>';
    }

    try {
        const canvas = await html2canvas(el, {
            scale: 3,
            useCORS: true,
            allowTaint: true,
            backgroundColor: null,
            logging: false,
        });

        const imageUri = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = filename;
        link.href = imageUri;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> <span>Tersimpan di Galeri!</span>';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 3000);
        }
    } catch (err) {
        console.error('Error generating card image:', err);
        alert('Gagal mendownload kartu: ' + err.message);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
};

window.printEcard = function(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Cetak E-Card Ekskul Pembda</title>
            <style>
                @page { size: auto; margin: 15mm; }
                body {
                    margin: 0;
                    padding: 20px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    font-family: system-ui, -apple-system, sans-serif;
                    background: #ffffff;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            </style>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body>
            <div style="max-width: 580px; width: 100%;">
                ${el.outerHTML}
            </div>
            <script>
                setTimeout(() => {
                    window.print();
                    window.close();
                }, 600);
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
};

window.shareEcard = async function(elementId, title = 'E-Card Ekskul Pembda', text = 'Kartu Anggota Resmi Ekskul Perguruan Pembda') {
    if (navigator.share) {
        try {
            const el = document.getElementById(elementId);
            if (el && window.html2canvas) {
                const canvas = await html2canvas(el, { scale: 2, useCORS: true, allowTaint: true });
                canvas.toBlob(async (blob) => {
                    if (blob && navigator.canShare && navigator.canShare({ files: [new File([blob], 'ecard.png', { type: 'image/png' })] })) {
                        const file = new File([blob], 'ecard-pembda.png', { type: 'image/png' });
                        await navigator.share({
                            title: title,
                            text: text,
                            files: [file]
                        });
                        return;
                    }
                    // Fallback to text share
                    await navigator.share({
                        title: title,
                        text: text + ' - https://perguruanpembda.com',
                        url: window.location.href
                    });
                });
            } else {
                await navigator.share({
                    title: title,
                    text: text,
                    url: window.location.href
                });
            }
        } catch (e) {
            console.log('Share dismissed or failed', e);
        }
    } else {
        // Fallback: Copy link
        navigator.clipboard.writeText(window.location.href);
        alert('Tautan halaman ekskul berhasil disalin ke clipboard!');
    }
};
