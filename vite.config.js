import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import fs from "fs";
import path from "path";
/* if you're using React */
// import react from '@vitejs/plugin-react';

const perPagesJs = () => {
  if (!fs.existsSync(path.resolve(__dirname, "assets/pages/"))) {
    return {};
  }
  const files = fs.readdirSync(path.resolve(__dirname, "assets/pages/"), {
    recursive: true,
  });
  const entries = {};
  files
    .filter((f) =>
      fs.lstatSync(path.resolve(__dirname, "assets/pages/", f)).isFile()
    )
    .forEach((file) => {
      const entryName = file
        .replace("pages/", "")
        .replaceAll("/", "_")
        .replace(/\.(js|ts|scss|css)$/, "_$1");

      entries[entryName] = path.join("./assets/pages/", file);
    });
  return entries;
};

// autoInject function will inject new entries when new files are added to the pages directory, then entry name is the file path with / replaced by _
let timer;
function clear() {
  clearTimeout(timer);
}
function schedule(fn, delay) {
  clear();
  timer = setTimeout(fn, delay);
}

function autoInject() {
  return {
    name: "auto-inject",
    apply: "serve",
    configureServer(server) {
      server.watcher.add(path.resolve(__dirname, "assets/pages"));
      server.watcher.on("add", (file) => {
        if (file.includes("assets/pages/")) {
          console.log("New file added: ", file);
          schedule(() => {
            server.restart();
          });
        }
      });
      server.watcher.on("unlink", (file) => {
        console.log("New file deleted: ", file);
        if (file.includes("assets/pages/")) {
          schedule(() => {
            server.restart();
          });
        }
      });
    },
  };
}

export default defineConfig({
  plugins: [
    /* react(), // if you're using React */
    symfonyPlugin({
      viteDevServerHostname: "localhost",
    }),
    autoInject(),
  ],
  build: {
    rollupOptions: {
      input: {
        app: "./assets/app.js",
        ...perPagesJs(),
      },
    },
  },
  server: {
    host: "0.0.0.0",
  },
});
