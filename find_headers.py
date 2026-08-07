import pypdf

def debug_headers(pdf_path):
    reader = pypdf.PdfReader(pdf_path)
    page = reader.pages[12] # P13
    data = []
    def visitor(text, cm, tm, font_dict, font_size):
        if text.strip():
            data.append({'x': tm[4], 'y': tm[5], 't': text.strip()})
    page.extract_text(visitor_text=visitor)
    
    # Look for the row that contains DPIB
    # Usually around Y = 400-600
    for w in sorted(data, key=lambda x: -x['y']):
        if w['t'] == 'DPIB':
            y_val = w['y']
            # Find all words on this Y
            row = [v for v in data if abs(v['y'] - y_val) < 5]
            row.sort(key=lambda x: x['x'])
            print(f"Header at Y={y_val}:")
            for h in row:
                print(f"X={h['x']:.1f} | {h['t']}")
            break

debug_headers(r"d:\laragon\www\pembdahub\Pembagian Tugas Semester Ganjil TP. 2025-2026.pdf")
