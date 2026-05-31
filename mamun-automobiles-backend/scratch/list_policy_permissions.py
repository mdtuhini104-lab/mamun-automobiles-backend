import os
import re

def find_permissions(policies_dir):
    permissions = set()
    pattern = re.compile(r"hasPermissionTo\(\s*['\"]([^'\"]+)['\"]")
    for file in os.listdir(policies_dir):
        if file.endswith('.php'):
            file_path = os.path.join(policies_dir, file)
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
                matches = pattern.findall(content)
                for m in matches:
                    permissions.add(m)
    return permissions

if __name__ == '__main__':
    backend_app_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    policies_dir = os.path.join(backend_app_dir, 'app', 'Policies')
    print("Permissions found in policies:")
    for p in sorted(find_permissions(policies_dir)):
        print(f"  - {p}")
