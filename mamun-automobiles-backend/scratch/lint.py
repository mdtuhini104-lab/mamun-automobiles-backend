import os
import subprocess
import sys

def lint_php_files(base_dir):
    error_found = False
    subdirs = ['app', 'config', 'routes', 'database']
    for s in subdirs:
        target = os.path.join(base_dir, s)
        if not os.path.exists(target):
            continue
        for root, dirs, files in os.walk(target):
            for file in files:
                if file.endswith('.php'):
                    file_path = os.path.join(root, file)
                    try:
                        result = subprocess.run(
                            ['php', '-l', file_path],
                            stdout=subprocess.PIPE,
                            stderr=subprocess.PIPE,
                            text=True
                        )
                        if result.returncode != 0:
                            print(f"Error in {file_path}:")
                            print(result.stdout)
                            print(result.stderr)
                            error_found = True
                    except Exception as e:
                        print(f"Failed to lint {file_path}: {e}")
                        error_found = True
    return error_found

if __name__ == '__main__':
    backend_app_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    print(f"Linting PHP files in {backend_app_dir}...")
    if lint_php_files(backend_app_dir):
        print("Linting failed with errors.")
        sys.exit(1)
    else:
        print("All PHP files are valid.")
        sys.exit(0)
