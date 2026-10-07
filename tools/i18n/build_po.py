"""Fills a POT file with the Russian translations in ru.py and writes the .po file.

Usage: python3 build_po.py <pot> <po> <PLUGIN|THEME> <ru.py>
Exits with an error listing every string that has no translation, so the
translation can never silently fall behind the code.
"""
import datetime, re, sys, importlib.util
spec = importlib.util.spec_from_file_location('ru', sys.argv[4]); ru = importlib.util.module_from_spec(spec); spec.loader.exec_module(ru)
pot, out, which = sys.argv[1], sys.argv[2], sys.argv[3]
table = getattr(ru, which)
text = open(pot, encoding='utf-8').read()

def esc(s): return s.replace('\\','\\\\').replace('"','\\"')
def unesc(s): return s.replace('\\"','"').replace('\\\\','\\')

blocks = text.split('\n\n')
header, entries, missing = blocks[0], [], []
header = re.sub(r'"Language: \\n"', '"Language: ru_RU\\\\n"', header)
header = header.replace('"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\\n"', '')
header = re.sub(r'(msgstr ""\n)', r'\1"Plural-Forms: nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);\\n"\n', header, count=1)
header = header.replace('"Language: \\n"','"Language: ru_RU\\n"')
header = re.sub(r'"Last-Translator: .*\\n"', '"Last-Translator: Yevhen Tiutiunnyk\\\\n"', header)
header = re.sub(r'"Language-Team: .*\\n"', '"Language-Team: Russian\\\\n"', header)
header = re.sub(r'"PO-Revision-Date: .*\\n"', '"PO-Revision-Date: ' + datetime.date.today().isoformat() + '\\\\n"', header)
if 'Language: ru_RU' not in header:
    header = header.replace('"Content-Type:', '"Language: ru_RU\\n"\n"Content-Type:', 1)
for block in blocks[1:]:
    if not block.strip(): continue
    ctx = re.search(r'^msgctxt "(.*)"$', block, re.M)
    mid = re.search(r'^msgid "(.*)"$', block, re.M)
    plural = re.search(r'^msgid_plural "(.*)"$', block, re.M)
    key = (unesc(ctx.group(1)) if ctx else None, unesc(mid.group(1)))
    if key not in table:
        missing.append(key); continue
    value = table[key]
    body = re.sub(r'^msgstr.*$', '', block, flags=re.M).rstrip('\n')
    if plural:
        if not isinstance(value, list) or len(value) != 3: missing.append(('needs 3 plural forms',)+key); continue
        body += '\n' + '\n'.join(f'msgstr[{i}] "{esc(v)}"' for i, v in enumerate(value))
    else:
        if isinstance(value, list): missing.append(('not plural',)+key); continue
        body += f'\nmsgstr "{esc(value)}"'
    entries.append(body)
if missing:
    print('MISSING', *missing, sep='\n'); sys.exit(1)
open(out, 'w', encoding='utf-8').write(header + '\n\n' + '\n\n'.join(entries) + '\n')
print('wrote', out, len(entries))
