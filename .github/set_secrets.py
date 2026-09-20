#!/usr/bin/env python3
"""
ตั้งค่า GitHub Secrets สำหรับ stc-edvt repo
วิธีใช้: python3 set_secrets.py
ต้องติดตั้ง GitHub CLI (gh) ก่อน
"""
import subprocess
import sys

REPO = "shellclub/stc-edvt"

SECRETS = {
    "SERVER_HOST": "122.154.78.137",
    "SERVER_USER": "root",
    "SERVER_PASSWORD": "technicalsuphanburi2023!!!",
}

def set_secret(name, value):
    result = subprocess.run(
        ["gh", "secret", "set", name, "--repo", REPO, "--body", value],
        capture_output=True,
        text=True
    )
    if result.returncode == 0:
        print(f"  ✅ {name} set successfully")
    else:
        print(f"  ❌ {name} failed: {result.stderr}")
        return False
    return True

def main():
    print(f"🔐 Setting GitHub Secrets for {REPO}...")
    print("-" * 40)
    
    all_ok = True
    for name, value in SECRETS.items():
        if not set_secret(name, value):
            all_ok = False
    
    print("-" * 40)
    if all_ok:
        print("✅ All secrets set successfully!")
    else:
        print("⚠️ Some secrets failed. Check errors above.")

if __name__ == "__main__":
    main()
