#!/bin/bash
# prepare-release.sh - 自動準備發布分支

set -e

echo "🌿 準備發布分支..."

# 檢查是否在 main 分支
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo "❌ 錯誤：必須在 main 分支執行此腳本"
    echo "請先切換到 main 分支：git checkout main"
    exit 1
fi

# 檢查是否有未提交的變更
if [ -n "$(git status --porcelain)" ]; then
    echo "❌ 錯誤：有未提交的變更"
    echo "請先提交變更："
    echo "  git add ."
    echo "  git commit -m '你的提交訊息'"
    exit 1
fi

# 獲取版本號
VERSION=$(grep "Version:" lithe-course.php | cut -d' ' -f4)
echo "📦 版本: $VERSION"

# 推送 main 分支
echo "🌿 推送 main 分支..."
git push origin main

# 切換到 release 分支
echo "🔄 切換到 release 分支..."
git checkout release

# 合併 main 分支的變更
echo "🌿 合併 main 分支的變更..."
git merge main --no-edit

# 重新設定 .gitignore（完全跟隨 .distignore 邏輯）
echo "🌿 清理發布檔案..."
cat > .gitignore << 'EOF'
# Development files - 跟隨 .distignore 邏輯
node_modules/
temp/
tests/
.git/
.github/
.idea/
.vscode/
*.log
.distignore
.gitignore

# Build tools
package-lock.json
composer.lock

# Build artifacts
*.zip
plugin-dist/

# OS cruft
.DS_Store
Thumbs.db

# 保留這個 .gitignore
!.gitignore
EOF

# 移除已追蹤的檔案（跟隨 .distignore 邏輯）
echo "🌿 移除開發檔案..."
git rm -r --cached node_modules 2>/dev/null || true
git rm -r --cached temp 2>/dev/null || true
git rm -r --cached tests 2>/dev/null || true
git rm -r --cached .git 2>/dev/null || true
git rm -r --cached .github 2>/dev/null || true
git rm -r --cached .idea 2>/dev/null || true
git rm -r --cached .vscode 2>/dev/null || true
git rm --cached package-lock.json 2>/dev/null || true
git rm --cached composer.lock 2>/dev/null || true
git rm --cached .distignore 2>/dev/null || true
git rm --cached .gitignore 2>/dev/null || true
git rm --cached prepare-release.sh 2>/dev/null || true
git rm --cached deploy-from-release.sh 2>/dev/null || true
git rm --cached README.md 2>/dev/null || true

# 提交清理
git add .
git commit -m "chore: 清理發布檔案 v$VERSION"

# 建立標籤
echo "🏷️ 建立版本標籤..."
git tag v$VERSION

# 推送
echo "📤 推送發布分支和標籤..."
git push origin release
git push origin v$VERSION

echo "✅ 發布分支準備完成！"
echo "🌿 現在可以部署到 WordPress.org"
echo "📋 使用: ./deploy-from-release.sh"
echo ""
echo "🔄 切換回 main 分支..."
git checkout main

echo "✅ 完成！你現在在 main 分支，可以繼續開發"