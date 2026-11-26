#!/usr/bin/env python3
"""Build an installable WordPress ZIP using only the plugin's shipped files."""
from pathlib import Path
import hashlib
import re
from zipfile import ZipFile, ZipInfo, ZIP_DEFLATED

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'el-crisol-mantenimiento'
PLUGIN = ROOT / SLUG
FILES = (
    'el-crisol-mantenimiento.php',
    'template.php',
    'styles.css',
    'uninstall.php',
    'readme.txt',
    'LICENSE',
    'assets/logo-transparent.png',
)

def build():
    header = (PLUGIN / FILES[0]).read_text()
    version_match = re.search(r'^\s*\* Version:\s*(\S+)', header, re.MULTILINE)
    if not version_match:
        raise ValueError('Plugin version missing')
    version = version_match.group(1)
    readme = (PLUGIN / 'readme.txt').read_text()
    if not re.search(r'^Stable tag:\s*' + re.escape(version) + r'\s*$', readme, re.MULTILINE):
        raise ValueError('Plugin version and readme stable tag differ')
    output = ROOT / 'build'
    output.mkdir(exist_ok=True)
    archive = output / (SLUG + '.zip')
    with ZipFile(archive, 'w', compression=ZIP_DEFLATED, compresslevel=9) as bundle:
        for name in FILES:
            source = PLUGIN / name
            if source.is_symlink() or not source.is_file():
                raise ValueError('Missing or unsafe plugin file: ' + name)
            entry = ZipInfo(SLUG + '/' + name, date_time=(2026, 1, 1, 0, 0, 0))
            entry.compress_type = ZIP_DEFLATED
            entry.create_system = 3
            entry.external_attr = 0o100644 << 16
            bundle.writestr(entry, source.read_bytes())
    with ZipFile(archive) as bundle:
        expected = {SLUG + '/' + name for name in FILES}
        if set(bundle.namelist()) != expected or bundle.testzip() is not None:
            raise ValueError('ZIP verification failed')
    digest = hashlib.sha256(archive.read_bytes()).hexdigest()
    print(f'Built {archive.relative_to(ROOT)} (version {version}, {len(FILES)} files)')
    print(f'SHA256 {digest}')
    return archive

if __name__ == '__main__':
    build()
