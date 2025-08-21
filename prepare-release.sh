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

# 檢查 release 分支是否存在
if git show-ref --verify --quiet refs/heads/release; then
    echo "🔄 切換到現有的 release 分支..."
    git checkout release
    
    # 合併 main 分支的變更
    echo "🌿 合併 main 分支的變更..."
    git merge main --no-edit
else
    echo "🆕 建立新的 release 分支..."
    git checkout -b release
fi

# 清理 release 分支，只保留需要的檔案
echo "🧹 清理發布檔案..."

# 移除不需要的檔案和資料夾
rm -rf node_modules temp tests .git .github .idea .vscode package-lock.json composer.lock .distignore prepare-release.sh deploy-from-release.sh README.md *.zip plugin-dist 2>/dev/null || true

# 建立簡潔的 .gitignore
cat > .gitignore << 'EOF'
# 忽略所有開發檔案
node_modules/
temp/
tests/
.git/
.github/
.idea/
.vscode/
*.log
.distignore
package-lock.json
composer.lock
*.zip
plugin-dist/
.DS_Store
Thumbs.db
prepare-release.sh
deploy-from-release.sh
README.md
EOF

# 提交清理後的版本
git add .
git commit -m "chore: 初始發布分支 v$VERSION"

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