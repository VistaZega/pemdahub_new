import pypdf

def scan_grid_x(pdf_path):
    reader = pypdf.PdfReader(pdf_path)
    page = reader.pages[12]
    data = []
    def visitor(text, cm, tm, font_dict, font_size):
        if text.strip():
            data.append({'x': tm[4], 'y': tm[5], 't': text.strip()})
    page.extract_text(visitor_text=visitor)
    
    classes = ['DPIB', 'TE', 'TKR', 'TSM', 'ACP', 'TJKT']
    xs = []
    # Find words containing these class markers
    for w in data:
        if any(c in w['t'].upper() for c in classes):
            if w['x'] > 100:
                xs.append(w)
                
    xs.sort(key=lambda x: (x['y'], x['x']))
    
    # Group by Y to see headers
    current_y = None
    row = []
    for w in xs:
        if current_y is None or abs(w['y'] - current_y) > 3:
            if row:
                print(f"Row at Y={current_y:.1f}: " + " | ".join([f"{v['x']:.1f}:{v['t']}" for v in sorted(row, key=lambda x: x['x'])]))
            current_y = w['y']
            row = [w]
        else:
            row.append(w)
    if row:
        print(f"Row at Y={current_y:.1f}: " + " | ".join([f"{v['x']:.1f}:{v['t']}" for v in sorted(row, key=lambda x: x['x'])]))

scan_grid_x(r"d:\laragon\www\pembdahub\Pembagian Tugas Semester Ganjil TP. 2025-2026.pdf")
