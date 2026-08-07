import pypdf

def debug_matrices(pdf_path):
    reader = pypdf.PdfReader(pdf_path)
    page = reader.pages[12]
    def visitor(text, cm, tm, font_dict, font_size):
        if 'SBD' in text:
            print(f"Text: '{text}' | CM: {cm} | TM: {tm}")
    page.extract_text(visitor_text=visitor)

debug_matrices(r"d:\laragon\www\pembdahub\Pembagian Tugas Semester Ganjil TP. 2025-2026.pdf")
